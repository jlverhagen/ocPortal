{$REQUIRE_JAVASCRIPT,javascript_profile}
{$REQUIRE_JAVASCRIPT,javascript_editing}
{$REQUIRE_CSS,ocf}

<div class="box"><div class="box_inner">
	<h3>Encrypted text</h3>

	{+START,IF_NON_EMPTY,{$_POST,decrypt}}
		{$DECRYPT,{CONTENT},{$_POST,decrypt}}
	{+END}

	{+START,IF_EMPTY,{$_POST,decrypt}}
		{+START,IF,{$JS_ON}}
			<p>
				<a href="javascript:decrypt_data('{CONTENT;^*}');" title="{!DECRYPT_DATA}: {!DESCRIPTION_DECRYPT_DATA=}">{!DECRYPT_DATA}</a>
			</p>
		{+END}
		{+START,IF,{$NOT,{$JS_ON}}}
			<p>JavaScript is required.</p>
		{+END}
	{+END}
</div></div>
