<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2012

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		unit_testing
 */

/**
 * ocPortal test case class (unit testing).
 */
class chatroom_test_set extends ocp_test_case
{
	var $chatroom_id;

	function setUp()
	{
		parent::setUp();
		require_code('chat');
		require_code('chat2');
		$this->chatroom_id=add_chatroom('test_message','test_chat_room',4,'','2,3,4,5,6,7,8,9,10','','','EN',0);
		$this->assertTrue('test_chat_room'==$GLOBALS['SITE_DB']->query_value('chat_rooms','room_name ',array('id'=>$this->chatroom_id)));
	}

	function testEditChatroom()
	{
		edit_chatroom($this->chatroom_id,'test message 1','test_chat_room1',4,'','2,3,4,5,6,7,8,9,10','','','EN');
		$this->assertTrue('test_chat_room1'==$GLOBALS['SITE_DB']->query_value('chat_rooms','room_name ',array('id'=>$this->chatroom_id)));
	}


	function tearDown()
	{
		delete_chatroom($this->chatroom_id);
		parent::tearDown();
	}
}
