{+START,IF_PASSED,SUBMITTER}{+START,IF_NON_EMPTY,{SUBMITTER}}
	{+START,IF,{$OCF}}
		{+START,IF,{$OR,{$ADDON_INSTALLED,ocf_avatars},{$IS_NON_EMPTY,{$AVATAR,{SUBMITTER}}}}}
			{$REQUIRE_JAVASCRIPT,javascript_ajax}

			<img class="embedded_mini_avatar" src="{$THUMBNAIL*,{$?,{$IS_EMPTY,{$AVATAR,{SUBMITTER}}},{$IMG,ocf_default_avatars/default},{$AVATAR,{SUBMITTER}}},50}" alt="" onmouseover="var _this=this; this.cancelled=false; load_snippet('member_tooltip&member_id={SUBMITTER%}',null,function(result) { if (typeof window.activate_tooltip!='undefined' &amp;&amp; !this.cancelled) activate_tooltip(_this,event,result.responseText,'auto',null,null,false,true); });" onmouseout="deactivate_tooltip(this,event); this.cancelled=true;" />
		{+END}
	{+END}
{+END}{+END}
