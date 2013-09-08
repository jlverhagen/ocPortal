<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2013

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		core_rich_media
 */

/*
Notes...
 - The cache_age property is not supported. It would significantly complicate the API and hurt performance, and we don't know a use case for it. The spec says it is optional to support.
*/

class Hook_media_rendering_oembed
{
	/**
	 * Find the media types this hook serves.
	 *
	 * @return integer	The media type(s), as a bitmask
	 */
	function get_media_type()
	{
		return MEDIA_TYPE_ALL;
	}

	/**
	 * See if we can recognise this mime type.
	 *
	 * @param  ID_TEXT	The mime type
	 * @param  ?array		The media signature, so we can go on this on top of the mime-type (NULL: not known)
	 * @return integer	Recognition precedence
	 */
	function recognises_mime_type($mime_type,$media_signature=NULL)
	{
		if ($mime_type=='text/html' || $mime_type=='application/xhtml+xml')
		{
			if ($media_signature!==NULL)
			{
				if ((($media_signature['t_json_discovery']!='') && (function_exists('json_decode'))) || ($media_signature['t_xml_discovery']!=''))
					return MEDIA_RECOG_PRECEDENCE_MEDIUM;
			}
		}
		return MEDIA_RECOG_PRECEDENCE_NONE;
	}

	/**
	 * See if we can recognise this URL pattern.
	 *
	 * @param  URLPATH	URL to pattern match
	 * @return integer	Recognition precedence
	 */
	function recognises_url($url)
	{
		if ($this->_find_oembed_endpoint($url)!==NULL) return MEDIA_RECOG_PRECEDENCE_MEDIUM;
		return MEDIA_RECOG_PRECEDENCE_NONE;
	}

	/**
	 * Provide code to display what is at the URL, in the most appropriate way.
	 *
	 * @param  URLPATH	URL to render
	 * @param  array		Attributes (e.g. width, height, length)
	 * @param  ?MEMBER	Member to run as
	 * @return tempcode	Rendered version
	 */
	function render($url,$attributes,$source_member=NULL)
	{
		$endpoint=$this->_find_oembed_endpoint($url);
		if ($endpoint===NULL) return $this->_fallback_render($url,$attributes,$source_member);

		// Work out the full endpoint URL to call
		$format_in_path=(strpos($endpoint,'{format}')!==false);
		$preferred_format=function_exists('json_decode')?'json':'xml';
		if ($format_in_path)
		{
			$endpoint=str_replace('{format}',$preferred_format,$endpoint);
		}
		if (strpos($endpoint,'?')===false)
		{
			$endpoint.='?url='.urlencode($url);
		} else
		{
			if ((strpos($endpoint,'?url=')===false) && (strpos($endpoint,'&url=')===false))
			{
				$endpoint.='&url='.urlencode($url);
			}
		}
		$endpoint.='&maxwidth='.urlencode(((array_key_exists('width',$attributes)) && ($attributes['width']!=''))?$attributes['width']:get_option('oembed_max_size'));
		$endpoint.='&maxheight='.urlencode(((array_key_exists('height',$attributes)) && ($attributes['height']!=''))?$attributes['height']:get_option('oembed_max_size'));
		if (!$format_in_path)
		{
			if (strpos($endpoint,'&format=')===false)
			{
				$endpoint.='&format='.urlencode($preferred_format);
			}
		}

		// Call endpoint
		global $HTTP_DOWNLOAD_MIME_TYPE;
		require_code('files');
		$result=http_download_file($endpoint,NULL,false);
		if ($result===NULL) return $this->_fallback_render($url,$attributes,$source_member);

		// Handle
		require_code('character_sets');
		$data=array();
		switch ($HTTP_DOWNLOAD_MIME_TYPE)
		{
			case 'text/xml':
			case 'text/xml+oembed':
				require_code('xml_storage');
				$parsed=new ocp_simple_xml_reader($result);
				list($root_tag,$root_attributes,,$this_children)=$parsed->gleamed;
				if ($root_tag=='oembed')
				{
					foreach ($this_children as $child)
					{
						list($key,,$val)=$child;
						$data[$key]=convert_to_internal_encoding($val,'utf-8');
					}
				}
				break;
			case 'application/json':
			case 'application/json+oembed':
			case 'text/javascript': // noembed uses this, naughty
				if (function_exists('json_decode'))
				{
					$_data=json_decode($result);
					if ($_data===NULL) return $this->_fallback_render($url,$attributes,$source_member);
					$data=array();
					foreach ($_data as $key=>$val) // It's currently an object, we want an array
					{
						if (is_null($val)) continue;
						$data[$key]=is_string($val)?convert_to_internal_encoding($val,'utf-8'):strval($val);
					}
				}
				break;
			default:
				return $this->_fallback_render($url,$attributes,$source_member);
		}

		// Validation
		if ((!array_key_exists('type',$data)) && (array_key_exists('thumbnail_url',$data))) // yfrog being weird
		{
			$data['type']='link';
		}
		if (!array_key_exists('type',$data)) return $this->_fallback_render($url,$attributes,$source_member); // E.g. an error result, with an "error" value - but we don't show errors as we just fall back instead
		switch ($data['type'])
		{
			case 'photo':
				if ((!array_key_exists('url',$data)) || (!array_key_exists('width',$data)) || (!array_key_exists('height',$data))) break;
				$url=$data['url'];
				if (array_key_exists('media_url',$data)) $url=$data['media_url']; // noembed uses this, naughty
				if (array_key_exists('thumbnail_url',$data)) $url=$data['thumbnail_url'];
				$map=array('width'=>$data['width'],'height'=>$data['height']);
				if (array_key_exists('description',$data)) $map['description']=$data['description']; // not official, but embed.ly has it
				elseif (array_key_exists('title',$data)) $map['description']=$data['title'];
				if (array_key_exists('thumbnail_width',$data)) $map['width']=$data['thumbnail_width'];
				if (array_key_exists('thumbnail_height',$data)) $map['height']=$data['thumbnail_height'];
				require_code('media_renderer');
				return render_media_url($url,$map,false,$source_member,MEDIA_TYPE_ALL,'image_websafe');

			case 'video':
				if ((!array_key_exists('width',$data)) || (!array_key_exists('height',$data))) break;
			case 'rich':
				if (!array_key_exists('html',$data)) break;

				// Check security
				$url_details=parse_url($url);
				$whitelist=explode(chr(10),get_option('oembed_html_whitelist'));
				if ((!in_array($url_details['host'],$whitelist)) && (!in_array(preg_replace('#^www\.#','',$url_details['host']),$whitelist)))
				{
					/*require_code('comcode_compiler');	We could do this but it's not perfect, it still has some level of trust
					$len=strlen($data['html']);
					filter_html(false,$GLOBALS['FORUM_DRIVER']->get_guest_id(),0,$len,$data['html'],true,false);*/
					$data['html']=strip_tags($data['html']);
				}

				return do_template('MEDIA_WEBPAGE_OEMBED_'.strtoupper($data['type']),array(
					'TITLE'=>array_key_exists('title',$data)?$data['title']:'',
					'HTML'=>$data['html'],
					'WIDTH'=>array_key_exists('width',$data)?$data['width']:'',
					'HEIGHT'=>array_key_exists('height',$data)?$data['height']:'',
					'URL'=>$url,
				));

			case 'link':
				if (!array_key_exists('thumbnail_url',$data))
					return $this->_fallback_render($url,$attributes,$source_member,array_key_exists('title',$data)?$data['title']:'');

				// embed.ly may show thumbnail details within a "link" type
				$url=$data['thumbnail_url'];
				$map=array();
				if (array_key_exists('thumbnail_width',$data)) $map['width']=$data['thumbnail_width'];
				if (array_key_exists('thumbnail_width',$data)) $map['height']=$data['thumbnail_height'];
				if (array_key_exists('description',$data)) $map['description']=$data['description']; // not official, but embed.ly has it
				elseif (array_key_exists('title',$data)) $map['description']=$data['title'];

				require_code('media_renderer');
				return render_media_url($url,$map,false,$source_member,MEDIA_TYPE_ALL,'image_websafe');
		}

		// Should not get here
		return $this->_fallback_render($url,$attributes,$source_member);
	}

	/**
	 * Provide code to display what is at the URL, when we fail to render with oEmbed.
	 *
	 * @param  URLPATH	URL to render
	 * @param  array		Attributes (e.g. width, height, length)
	 * @param  ?MEMBER	Member to run as (NULL: current member)
	 * @param  string		Text to show the link with
	 * @return tempcode	Rendered version
	 */
	function _fallback_render($url,$attributes,$source_member,$link_captions_title='')
	{
		if ($link_captions_title=='')
		{
			require_code('files2');
			$meta_details=get_webpage_meta_details($url);
			$link_captions_title=$meta_details['t_title'];
			if ($link_captions_title=='') $link_captions_title=$url;
		}

		require_code('comcode_renderer');
		if (is_null($source_member)) $source_member=get_member();
		$comcode='';
		$url_tempcode=new ocp_tempcode();
		$url_tempcode->attach($url);
		return _do_tags_comcode('url',array('param'=>$link_captions_title),$url_tempcode,false,'',0,$source_member,false,$GLOBALS['SITE_DB'],$comcode,false,false,false);
	}

	/**
	 * Find an oEmbed endpoint for a URL.
	 *
	 * @param  URLPATH	URL to find the oEmbed endpoint for
	 * @return ?URLPATH	Endpoint UR (NULL: none found)
	 */
	function _find_oembed_endpoint($url)
	{
		// Hard-coded
		$_oembed_manual_patterns=get_option('oembed_manual_patterns');
		$oembed_manual_patterns=explode(chr(10),$_oembed_manual_patterns);
		foreach ($oembed_manual_patterns as $oembed_manual_pattern)
		{
			if (strpos($oembed_manual_pattern,'=')!==false)
			{
				if (strpos($oembed_manual_pattern,' = ')!==false)
				{
					list($url_pattern,$endpoint)=explode(' = ',$oembed_manual_pattern,2);
				} else
				{
					$url_pattern=preg_replace('#(.*)=.*$#','${1}',$oembed_manual_pattern); // Before last =
					$endpoint=preg_replace('#^.*=#','',$oembed_manual_pattern); // After last =
				}
				if (@preg_match('#^'.str_replace('#','\#',$url_pattern).'$#',$url)!=0)
				{
					return $endpoint;
				}
			}
		}

		// Auto-discovery
		require_code('files2');
		$media_signature=get_webpage_meta_details($url);
		$mime_type=$media_signature['t_mime_type'];
		if ($mime_type=='text/html' || $mime_type=='application/xhtml+xml')
		{
			if (($media_signature['t_json_discovery']!='') && (function_exists('json_decode')))
				return $media_signature['t_json_discovery'];
			if ($media_signature['t_xml_discovery']!='')
				return $media_signature['t_xml_discovery'];
		}

		return NULL;
	}

}
