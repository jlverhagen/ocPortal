{+START,IF,{ALLOW_REMOVE}}
	<form id="feed_remove_{LIID*}" class="activities_remove" action="{$PAGE_LINK*,:start}" method="post" onsubmit="return s_update_remove(event,{LIID*});">
		<input class="remove_cross" type="submit" value="{!REMOVE}" />
	</form>
{+END}

<div class="activities_avatar_box">
	{+START,IF_NON_EMPTY,{ADDON_ICON}}
		<img src="{$THUMBNAIL*,{ADDON_ICON},36x36,addon_icon_normalise,,,pad,both,#FFFFFF00}" />
	{+END}
	{+START,IF_EMPTY,{ADDON_ICON}}
		<img src="{$THUMBNAIL*,{$IMG,bigicons/edit_one},36x36,addon_icon_normalise,,,pad,both,#FFFFFF00}" />
	{+END}

	{+START,SET,commented_out}
		{+START,IF_EMPTY,{MEMPIC}}{+START,IF_NON_EMPTY,{$IMG,ocf_default_avatars/default_set/ocp_fanatic,0,,1}}
			<img src="{$THUMBNAIL*,{$IMG,ocf_default_avatars/default_set/ocp_fanatic,0,,1},36x36,addon_avatar_normalise,,,pad,both,#FFFFFF00}" />
		{+END}{+END}
		{+START,IF_NON_EMPTY,{MEMPIC}}
			<img src="{$THUMBNAIL*,{MEMPIC},36x36,addon_avatar_normalise,,,pad,both,#FFFFFF00}" />
		{+END}
	{+END}
</div>

<div class="activities_line">
	{+START,SET,commented_out}
		<div class="activity_name left">
			<a href="{MEMBER_URL*}">{USERNAME*}</a>
		</div>
	{+END}

	<div class="activity_time right">
		{$MAKE_RELATIVE_DATE*,{DATETIME},1} {!AGO}
	</div>

	<div class="activities_content">
		{$,The main message}
		{+START,IF,{$EQ,{LANG_STRING},RAW_DUMP}}
			{+START,IF,{$EQ,{MODE},all}}
				{!ACTIVITY_SAYS,<a href="{MEMBER_URL*}">{USERNAME*}</a>,{BITS}}
			{+END}
			{+START,IF,{$NEQ,{MODE},all}}
				{BITS}
			{+END}
		{+END}
		{+START,IF,{$NEQ,{LANG_STRING},RAW_DUMP}}
			{$,Because it is being included, the including templates preprocessor will hit the SET but without any data, so we have an IF_PASSED}
			{+START,SET,named}{+START,IF_PASSED,MEMBER_ID}{$OR,{$NEQ,{MEMBER_IDS},{MEMBER_ID}},{$EQ,{MODE},all}}{+END}{+END}

			{+START,IF,{$GET,named}}
				{!ACTIVITY_HAS,<a href="{MEMBER_URL*}">{USERNAME*}</a>,{$LCASE,{$SUBSTR,{BITS},0,1}}{$SUBSTR,{BITS},1}}
			{+END}
			{+START,IF,{$NOT,{$GET,named}}}
				{BITS}
			{+END}
		{+END}
	</div>
</div>
