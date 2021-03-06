<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2012

 See text/EN/licence.txt for full licencing information.

*/

class Hook_cron_user_import
{

	/**
	 * Standard modular run function for CRON hooks. Searches for tasks to perform.
	 */
	function run()
	{
		require_code('user_import');

		if (!USER_IMPORT_ENABLED) return;

		$last=get_value('last_user_import');
		if ((is_null($last)) || ($last<time()-60*USER_IMPORT_MINUTES))
		{
			set_value('last_user_import',strval(time()));

			do_user_import();
		}
	}

}


