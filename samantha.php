<?php

//
// Complex example to show how OOP can be used with xBot
// This can be used to relay 2 channels on different networks
//

set_time_limit( 0 );
error_reporting( E_ALL ^ E_NOTICE );
// set time limit to 0

include( 'src/xbot.php' );
include( 'src/events.php' );
include( 'src/timer.php' );
// include xbot framework

$bot = new bot;

class bot
{

	public $xbot;
	static public $config;
	static public $quiet = false;
	static public $db;
	static public $queries = 0;
	static public $buffer = array();
	// give $xbot framework a var; so we can use it all around the class.

	/*
	* __construct
	*
	* @params
	* void
	*/
	public function __construct()
	{
		$this->xbot = new xbot;
		// new xbot;

		self::$config = array(
			'networks' 	=> array(
				'irc.ircnode.org'	=> array(
					'port' 			=> '6667',

					'nick' 			=> 'Sam',
					'ident' 		=> 'sam',
					'real' 			=> 'Samantha',

					'chans' 		=> array(
						'#lobby' => '',
						'#acora'	=> '',
					),
					// channels
				),
				// network one
			),

			'ctcp'		=> array(
				'version'	=> 'Samantha v0.1 powered by xBot Framework',
			),
			// ctcp replies

			'info_info'			=> 'There are {items} items in my database. I\'ve been online for {uptime} and I\'ve had {queries} queries since I\'ve been online.',

			'info_replies'		=> array(
				'I heard {key} {value}',
				'{key} {value} apparently.',
				'{key} {value}... I think',
				'Someone said {key} {value}',
			),

			'dont_know_replies'	=> array(
				'Sorry {nick}, no idea what {key} is',
				'{key}? no idea {nick}',
			),

			'info_notnew'		=> array(
				'I thought {key} {value}',
				'Hmm, {key} {value} ya cretin',
			),

			'info_heard'		=> array(
				'{setby} told me about {key} on {date}',
				'About....{date}, I think it was {setby} that said it',
				'I think it was {setby} that said it, way back on {date}',
			),

			'info_confirm'		=> array(
				'Righto',
				'Aye, ok',
				'You sure? Sounds a bit doggo..',
				'Ok, {nick}',
			),

			'info_locked'		=> array(
				'GTFO, {nick}',
				'Don\'t be silly, since when did you have control over me?',
			),

			'info_forgot'		=> array(
				'I forgot {key}',
			),

			'info_reserved'		=> array(
				'Yeah, like I am going to remember that',
				'What on earth are you trying to do you decrepit fool.'
			),

			'quiet_replies'		=> array(
				'Doggo, fine..',
				'Wake me up later am gan to kip!',
			),

			'wakeup_replies'	=> array(
				'Cannot be arsed with work today..',
				'Hey ya bunch of faggets',
			),
			// info replies

			'bot_triggers'		=> array(
				'samantha,',
				'samantha:',
				'samantha',
				'sam,',
				'sam:',
				'sam',
				'samantha2,',
				'samantha2:',
				'samantha2',
			),
			// bot triggers, LOWER CASE ONLY

			'system_phrases'	=> array(
				//'add git',
				//'del git',
				'quote',
				'lock',
				'unlock',
				'join',
				'part',
				'nick',
				'invite',
				'forget',
				'status',
				'wakeup',
				'quiet',
				'info',
			),
			// system-phrases

			'mysql' 			=> array(
				'host' 	=> 'localhost',
				'user'	=> 'root',
				'pass'	=> '',
				'db'	=> 'samantha',
				'table' => 'infobot',
			),
			// mysql

			'admin_hosts'		=> array(
				'wl562-633.members.linode.com',
			),
			// admin hosts
		);
		// build a config array

		self::$db = mysql_connect( self::$config['mysql']['host'], self::$config['mysql']['user'], self::$config['mysql']['pass'] );
		mysql_select_db( self::$config['mysql']['db'], self::$db );
		// connect to mysql

		$this->xbot->connect( self::$config );
		// connect the bot

		$this->xbot->main( 'bot', 'main' );
		// boot the main loop w/ a callback
	}

	/*
	* log_chans
	*
	* @params
	* $xbot - object passed from xbot::main()
	*/
	public function log_chans( $ircdata )
	{
		$write = false;
		$timestamp = date( 'H:i:s', time() );
		$data = '';

		if ( $ircdata->type == 'join' )
		{
			$data = $ircdata->nick . ' (' . $ircdata->ident . '@' . $ircdata->host . ') joined ' . $ircdata->channel;

			$write = true;
		}
		// join
		else if ( $ircdata->type == 'part' )
		{
			$data = $ircdata->nick . ' (' . $ircdata->ident . '@' . $ircdata->host . ') left ' . $ircdata->channel;
			$write = true;
		}
		// part
		else if ( $ircdata->type == 'quit' )
		{
			$data = $ircdata->nick . ' (' . $ircdata->ident . '@' . $ircdata->host . ') left irc: ' . $ircdata->message;
			$write = true;
		}
		// quit
		else if ( $ircdata->type == 'mode' && $ircdata->channel[0] == '#' )
		{
			$data = $ircdata->channel . ': mode change \'' . $ircdata->mode .  '\' by ' . $ircdata->nick . '!' . $ircdata->ident . '@' . $ircdata->host;
			$write = true;
		}
		// mode
		else if ( $ircdata->type == 'privmsg' && $ircdata->target[0] == '#' )
		{
			$data = '<' . $ircdata->nick . '> ' . $ircdata->message;
			$write = true;

			if ( isset( self::$buffer[$ircdata->target] ) && count( self::$buffer[$ircdata->target] ) >= 31 )
				$shift = array_shift( self::$buffer[$ircdata->target] );
				
			self::$buffer[$ircdata->target][] .= $data;
		}
		// privmsg to #
		
		if ( $write )
		{
			$data = '[' . $timestamp . '] ' . $data . "\r\n";

			if ( $ircdata->type == 'privmsg' )
				$file = substr( $ircdata->target, 1 ) . '.log.' . date( 'dMY', time() );
			else
				$file = substr( $ircdata->channel, 1 ) . '.log.' . date( 'dMY', time() );
			
			$handle = fopen( '/home/ricki/sam/logs/' . $file . '.txt', 'a' );
			fwrite( $handle, $data );
			fclose( $handle );
			// open file, write, close file
		}
		// write data to file
	}

	/*
	* main
	*
	* @params
	* $xbot - object passed from xbot::main()
	* $ircdata - object passed from xbot::main()
	*/
	public function main( $xbot, $ircdata )
	{
		if ( $xbot->events->on_ping( $xbot->ircdata ) )
		{
			if ( !mysql_ping( self::$db ) )
			{
				mysql_close( self::$db );
				self::$db = mysql_connect( self::$config['mysql']['host'], self::$config['mysql']['user'], self::$config['mysql']['pass'] );
				mysql_select_db( self::$config['mysql']['db'], self::$db );
				// connect to mysql
			}
		}
		// ping

		self::log_chans( $ircdata );

		if ( $ircdata->type == 'privmsg' && $ircdata->target[0] == '#' )
		{
			$message = explode( ' ', $ircdata->message );
			$nmessage = $message;

			if ( in_array( strtolower( $message[0] ), self::$config['bot_triggers'] ) )
			{
				unset( $nmessage[0] );
				$rmessage = implode( ' ', $nmessage );

				if ( strtolower( $message[1] ) == 'quote' )
				{
					if ( ( isset( $message[2] ) && isset( $message[3] ) && is_numeric( $message[2] ) && is_numeric( $message[3] ) ) && $message[2] >= 0 && $message[3] <= 30 && $message[3] > $message[2] )
					{
						$from = $message[2];
						$to = $message[3];
						$chan = $ircdata->target;
						// set some variables
					
						$mybuffer = self::$buffer[$chan];
						$pop = array_pop( $mybuffer );
						$mybuffer = array_reverse( $mybuffer );
						// do some SHUFFLING!!
						
						$mybuffer = array_slice( $mybuffer, $from, $to );
						//$mybuffer = implode( "\r\n", $mybuffer );
						// get what we need.
						
						// create a new cURL resource
						/*$ch = curl_init();

						// set URL and other appropriate options
						curl_setopt( $ch, CURLOPT_URL, 'http://bash.org/?add' );
						curl_setopt( $ch, CURLOPT_POST, 1 );
						curl_setopt( $ch, CURLOPT_POSTFIELDS, 'newquote='.url );

						// grab URL and pass it to the browser
						curl_exec( $ch );

						// close cURL resource, and free up system resources
						curl_close( $ch );*/
						
						return false;
					}
					// it is..
					else
					{
						return false;
					}
					// it isnt.
					// check if the format is right..
				}
				// sam, quote.

				if ( strtolower( $message[1] ) == 'info' )
				{
					$num_of_items = mysql_query( "SELECT `id` FROM `".self::$config['mysql']['table']."`" );
					$num_of_items = mysql_num_rows( $num_of_items );

					$uptime = self::format_time( timer::$uptime );

					$reply = self::$config['info_info'];
					$reply = str_replace( '{items}', $num_of_items, $reply );
					$reply = str_replace( '{uptime}', $uptime, $reply );
					$reply = str_replace( '{queries}', self::$queries, $reply );

					self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
					// msg to the channel.
					return false;
				}
				// sam, info.

				if ( strtolower( $message[1] ) == 'join' )
				{
					if ( !in_array( $ircdata->host, self::$config['admin_hosts'] ) )
					{
						$reply = array_rand( self::$config['info_locked'] );
						$reply = self::$config['info_locked'][$reply];
						$reply = str_replace( '{nick}', $ircdata->nick, $reply );
						// find a random reply, and replace {shit} with shit, etc. lol

						self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
						// msg to the channel.
						return false;
					}
					// is a non admin trying to fuck around with our shit? :/

					$chan = $message[2];
					$key = $message[3];
					// chan, and key, if a key is needed

					if ( $chan == null )
						return false;
					// error

					$xbot->join( $ircdata->from, $chan, $key );
					// join the channel :)
					return false;
				}
				// Samantha, join

				if ( strtolower( $message[1] ) == 'part' )
				{
					if ( !in_array( $ircdata->host, self::$config['admin_hosts'] ) )
					{
						$reply = array_rand( self::$config['info_locked'] );
						$reply = self::$config['info_locked'][$reply];
						$reply = str_replace( '{nick}', $ircdata->nick, $reply );
						// find a random reply, and replace {shit} with shit, etc. lol

						self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
						// msg to the channel.
						return false;
					}
					// is a non admin trying to fuck around with our shit? :/

					$chan = $message[2];
					$message = $xbot->get_data_after( $message, 3 );
					// chan, and message

					if ( $chan == null )
						return false;
					// error

					$xbot->part( $ircdata->from, $chan, $message );
					// part the channel :)
					return false;
				}
				// Samantha, part

				if ( strtolower( $message[1] ) == 'nick' )
				{
					if ( !in_array( $ircdata->host, self::$config['admin_hosts'] ) )
					{
						$reply = array_rand( self::$config['info_locked'] );
						$reply = self::$config['info_locked'][$reply];
						$reply = str_replace( '{nick}', $ircdata->nick, $reply );
						// find a random reply, and replace {shit} with shit, etc. lol

						self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
						// msg to the channel.
						return false;
					}
					// is a non admin trying to fuck around with our shit? :/

					$new_nick = $message[2];
					// chan, and message

					if ( $new_nick == null )
						return false;
					// error

					self::$config['networks'][$from]['nick'] = $new_nick;
					$xbot->nick( $ircdata->from, $new_nick );
					// change nick
					return false;
				}
				// Samantha, nick

				if ( strtolower( $message[1] ) == 'quiet' )
				{
					if ( self::$quiet )
					{
						return false;
					}
					// is samantha in quiet mode?!

					if ( !in_array( $ircdata->host, self::$config['admin_hosts'] ) )
					{
						$reply = array_rand( self::$config['info_locked'] );
						$reply = self::$config['info_locked'][$reply];
						$reply = str_replace( '{nick}', $ircdata->nick, $reply );
						// find a random reply, and replace {shit} with shit, etc. lol

						self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
						// msg to the channel.
						return false;
					}
					// is a non admin trying to fuck around with our shit? :/

					self::$quiet = true;

					$reply = array_rand( self::$config['quiet_replies'] );
					$reply = self::$config['quiet_replies'][$reply];
					$reply = str_replace( '{nick}', $ircdata->nick, $reply );
					// find a random reply, and replace {shit} with shit, etc. lol

					self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
					// msg to the channel.
					return false;
				}
				// Samantha, quiet

				if ( strtolower( $message[1] ) == 'wakeup' )
				{
					if ( !self::$quiet )
					{
						return false;
					}
					// is samantha in quiet mode?!

					if ( !in_array( $ircdata->host, self::$config['admin_hosts'] ) )
					{
						$reply = array_rand( self::$config['info_locked'] );
						$reply = self::$config['info_locked'][$reply];
						$reply = str_replace( '{nick}', $ircdata->nick, $reply );
						// find a random reply, and replace {shit} with shit, etc. lol

						self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
						// msg to the channel.
						return false;
					}
					// is a non admin trying to fuck around with our shit? :/

					self::$quiet = false;

					$reply = array_rand( self::$config['wakeup_replies'] );
					$reply = self::$config['wakeup_replies'][$reply];
					$reply = str_replace( '{nick}', $ircdata->nick, $reply );
					// find a random reply, and replace {shit} with shit, etc. lol

					self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
					// msg to the channel.
					return false;
				}
				// Samantha, wakeup

				if ( self::$quiet )
					return false;
				// is samantha in quiet mode?!

				if ( strtolower( $message[1] ) == 'who' && strtolower( $message[2] ) == 'told' && strtolower( $message[3] ) == 'you' && strtolower( $message[4] ) == 'about' && isset( $message[5] ) )
				{
					$key = $xbot->get_data_after( $message, 5 );
					// could be a "shauns feet smell of cheese" < eg, multiple keys

					if ( substr( $key, -1, 1 ) == '?' || substr( $key, -1, 1 ) == '.' || substr( $key, -1, 1 ) == '!' ) $key = substr( $key, 0, -1 );
					// replace the value shit
					$key = mysql_real_escape_string( $key );
					// escape the string, so theres no crap in it.

					$info_query = mysql_query( "SELECT `key`,`value`,`date`,`setby`,`locked` FROM `".self::$config['mysql']['table']."` WHERE `key` = '".$key."'" );
					// have we found a reply?

					if ( mysql_num_rows( $info_query ) == 0 )
					{
						$reply = array_rand( self::$config['dont_know_replies'] );
						$reply = self::$config['dont_know_replies'][$reply];
						$reply = str_replace( '{nick}', $ircdata->nick, $reply );
						$reply = str_replace( '{key}', $key, $reply );
						// find a random reply, and replace {shit} with shit, etc. lol

						self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
						// msg to the channel.
						return false;
					}
					else
					{
						$info_row = mysql_fetch_array( $info_query );
						// grab the row

						self::msg( $xbot, $ircdata->from, $ircdata->target, $info_row['setby'].' told me about '.$key.' on '.date( 'F j, Y, g:i a', $info_row['date'] ) );
						return false;
					}
				}
				// Samantha, who told you about *

				if ( strtolower( $message[1] ) == 'forget' && isset( $message[2] ) )
				{
					$key = mysql_real_escape_string( $xbot->get_data_after( $message, 2 ) );
					// escape the string, so theres no crap in it.

					$info_query = mysql_query( "SELECT `key`,`locked` FROM `".self::$config['mysql']['table']."` WHERE `key` = '".$key."'" );
					// have we found a reply?

					if ( mysql_num_rows( $info_query ) == 0 )
					{
						$reply = array_rand( self::$config['dont_know_replies'] );
						$reply = self::$config['dont_know_replies'][$reply];
						$reply = str_replace( '{nick}', $ircdata->nick, $reply );
						$reply = str_replace( '{key}', $key, $reply );
						// find a random reply, and replace {shit} with shit, etc. lol

						self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
						// msg to the channel.
						return false;
					}
					// no we haven't
					else
					{
						$info_row = mysql_fetch_array( $info_query );
						// grab the row

						if ( $info_row['locked'] == 1 && !in_array( $ircdata->host, self::$config['admin_hosts'] ) )
						{
							$reply = array_rand( self::$config['info_locked'] );
							$reply = self::$config['info_locked'][$reply];
							$reply = str_replace( '{nick}', $ircdata->nick, $reply );
							// find a random reply, and replace {shit} with shit, etc. lol

							self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
							// msg to the channel.
							return false;
						}
						// is it locked? :/

						mysql_query( "DELETE FROM `".self::$config['mysql']['table']."` WHERE `key` = '".$key."'" );
						// delete the record

						$reply = array_rand( self::$config['info_forgot'] );
						$reply = self::$config['info_forgot'][$reply];
						$reply = str_replace( '{key}', $key, $reply );
						// find a random reply, and replace {shit} with shit, etc. lol

						self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
						// msg to the channel.
						return false;
					}
					// yes we have
				}
				// Samantha, forget

				if ( strtolower( $message[1] ) == 'lock' || strtolower( $message[1] ) == 'unlock' && isset( $message[2] ) )
				{
					if ( !in_array( $ircdata->host, self::$config['admin_hosts'] ) )
					{
						$reply = array_rand( self::$config['info_locked'] );
						$reply = self::$config['info_locked'][$reply];
						$reply = str_replace( '{nick}', $ircdata->nick, $reply );
						// find a random reply, and replace {shit} with shit, etc. lol

						self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
						// msg to the channel.
						return false;
					}
					// is a non admin trying to fuck around with our shit? :/

					$key = mysql_real_escape_string( $xbot->get_data_after( $message, 2 ) );
					// escape the string, so theres no crap in it.

					$info_query = mysql_query( "SELECT `key`,`locked` FROM `".self::$config['mysql']['table']."` WHERE `key` = '".$key."'" );
					// have we found a reply?

					if ( mysql_num_rows( $info_query ) == 0 )
					{
						$reply = array_rand( self::$config['dont_know_replies'] );
						$reply = self::$config['dont_know_replies'][$reply];
						$reply = str_replace( '{nick}', $ircdata->nick, $reply );
						$reply = str_replace( '{key}', $key, $reply );
						// find a random reply, and replace {shit} with shit, etc. lol

						self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
						// msg to the channel.
						return false;
					}
					// no we haven't
					else
					{
						$tinyint = ( $message[1] == 'lock' ) ? 1 : 0;
						mysql_query( "UPDATE `".self::$config['mysql']['table']."` SET `locked` = '".$tinyint."' WHERE `key` = '".$key."'" );
						// update

						$reply = array_rand( self::$config['info_confirm'] );
						$reply = self::$config['info_confirm'][$reply];
						$reply = str_replace( '{nick}', $ircdata->nick, $reply );
						// find a random reply, and replace {shit} with shit, etc. lol

						self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
						// msg to the channel.
						return false;
					}
					// yes we have
				}
				// Samantha, lock * && Samantha, unlock *

				if ( preg_match( '/(.*): (.*)/is', $rmessage ) )
				{
					$keys = explode( ':', $rmessage, 2 );
					$key = mysql_real_escape_string( trim( $keys[0] ) );
					$key = str_replace( '\\', '', $key ); // replace \ with '' because \ is our reply delimeter.
					$value = mysql_real_escape_string( trim( $keys[1] ) );
					// escape the string, so theres no crap in it.

					if ( in_array( $key, self::$config['system_phrases'] ) )
					{
						$reply = array_rand( self::$config['info_reserved'] );
						$reply = self::$config['info_reserved'][$reply];
						// find a random reply

						self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
						// msg to the channel.
						return false;
					}
					// is this a reserved word?

					$info_query = mysql_query( "SELECT `key`,`value`,`locked` FROM `".self::$config['mysql']['table']."` WHERE `key` = '".$key."'" );
					// have we found a reply?

					if ( mysql_num_rows( $info_query ) == 0 )
					{
						$setby = mysql_real_escape_string( $ircdata->nick );
						$date = time();

						mysql_query( "INSERT INTO `".self::$config['mysql']['table']."` (`key`,`value`,`setby`,`date`) VALUES('".$key."','".$value."','".$setby."','".$date."')" );
						// update

						$reply = array_rand( self::$config['info_confirm'] );
						$reply = self::$config['info_confirm'][$reply];
						$reply = str_replace( '{nick}', $ircdata->nick, $reply );
						// find a random reply, and replace {shit} with shit, etc. lol

						self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
						// msg to the channel.
						return false;
					}
					// no we haven't
					else
					{
						$info_row = mysql_fetch_array( $info_query );
						// fetch the row

						if ( strtolower( trim( $info_row['value'] ) ) == strtolower( trim( $value ) ) )
						{
							self::msg( $xbot, $ircdata->from, $ircdata->target, 'I know :D' );
							// msg to the channel.
							return false;
						}
						// we know -.-

						$reply = array_rand( self::$config['info_notnew'] );
						$reply = self::$config['info_notnew'][$reply];
						$reply = str_replace( '{nick}', $ircdata->nick, $reply );
						$reply = str_replace( '{key}', stripslashes( $info_row['key'] ), $reply );
						$reply = str_replace( '{value}', stripslashes( $info_row['value'] ), $reply );
						// find a random reply, and replace {shit} with shit, etc. lol

						self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
						// msg to the channel.
						return false;
					}
					// yes we have*/
				}
				// Samantha, *: * (assignment) (changed this, easier i guess)

				if ( isset( $message[1] ) && strpos( $rmessage, ':' ) === false )
				{
					$keys = preg_split( '/\\\/si', $xbot->get_data_after( $message, 1 ), -1, PREG_SPLIT_NO_EMPTY );
					$key = trim( $keys[0] );
					// could be a "shauns feet smell of cheese" < eg, multiple keys

					++self::$queries;
					if ( substr( $key, -1, 1 ) == '?' || substr( $key, -1, 1 ) == '.' || substr( $key, -1, 1 ) == '!' ) $key = substr( $key, 0, -1 );
					// replace the value shit
					$key = mysql_real_escape_string( $key );
					// escape the string, so theres no crap in it.

					$info_query = mysql_query( "SELECT `key`,`value`,`locked`,`setby` FROM `".self::$config['mysql']['table']."` WHERE `key` = '".$key."'" );
					// have we found a reply?

					if ( mysql_num_rows( $info_query ) == 0 )
					{
						$reply = array_rand( self::$config['dont_know_replies'] );
						$reply = self::$config['dont_know_replies'][$reply];
						$reply = str_replace( '{nick}', $ircdata->nick, $reply );
						$reply = str_replace( '{key}', $key, $reply );
						// find a random reply, and replace {shit} with shit, etc. lol

						self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
						// msg to the channel.
						return false;
					}
					// no we haven't
					else
					{
						$info_row = mysql_fetch_array( $info_query );
						// fetch the row

						$reply = array_rand( self::$config['info_replies'] );
						$reply = self::$config['info_replies'][$reply];
						$reply = str_replace( '{nick}', $ircdata->nick, $reply );
						$reply = str_replace( '{key}', stripslashes( $info_row['key'] ), $reply );
						// find a random reply, and replace {shit} with shit, etc. lol
						$value = stripslashes( $info_row['value'] );
						$value = str_replace( '<reply>', trim( $keys[1] ), $value );
						$value = str_replace( '<encode_reply>', str_replace( ' ', '+', trim( $keys[1] ) ), $value );
						$value = str_replace( '<wiki_encode>', str_replace( ' ', '_', trim( $keys[1] ) ), $value );
						$value = str_replace( '<nick>', $info_row['setby'], $value );
						$value = str_replace( '<me>', bot::$config['networks']['irc.gamergrid.net']['nick'], $value );
						$value = str_replace( '<who>', $ircdata->nick, $value );
						// replace <reply> and stuff

						$reply = str_replace( '{value}', $value, $reply );
						// replace {value} last, because we've modified it with expansions :D

						if ( trim( substr( $value, 0, 8 ) ) == '<action>' )
							$xbot->action( $ircdata->from, $ircdata->target, substr( $value, 8 ) );
						else
							self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
						// msg to the channel.
						return false;
					}
					// yes we have
				}
				// Samantha, * (phrase)
			}
			// Samantha, message here

			if ( self::$quiet )
				return false;
			// is samantha in quiet mode?!

			unset( $nmessage[0], $nmessage[1] );
			$nrmessage = implode( ' ', $nmessage );

			if ( strtolower( $message[0] ) == 'no' && in_array( strtolower( $message[1] ), self::$config['bot_triggers'] ) && preg_match( '/(.*) \b('.self::$config['assignment_regex'].')\b/is', $nrmessage ) )
			{
				$keys = preg_split( '/\b('.self::$config['assignment_regex'].')\b/i', $nrmessage, -1, PREG_SPLIT_DELIM_CAPTURE );
				$key = mysql_real_escape_string( trim( $keys[0] ) );
				$key = str_replace( '\\', '', $key ); // replace \ with '' because \ is our reply delimeter.
				// escape the string, so theres no crap in it.

				if ( in_array( $key, self::$config['system_phrases'] ) )
				{
					$reply = array_rand( self::$config['info_reserved'] );
					$reply = self::$config['info_reserved'][$reply];
					// find a random reply

					self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
					// msg to the channel.
					return false;
				}
				// is this a reserved word?

				$info_query = mysql_query( "SELECT `key`,`value`,`locked` FROM `".self::$config['mysql']['table']."` WHERE `key` = '".$key."'" );
				// have we found a reply?

				if ( mysql_num_rows( $info_query ) == 0 )
				{
					$reply = array_rand( self::$config['dont_know_replies'] );
					$reply = self::$config['dont_know_replies'][$reply];
					$reply = str_replace( '{nick}', $ircdata->nick, $reply );
					$reply = str_replace( '{key}', $key, $reply );
					// find a random reply, and replace {shit} with shit, etc. lol

					self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
					// msg to the channel.
					return false;
				}
				// no we haven't
				else
				{
					$info_row = mysql_fetch_array( $info_query );

					if ( $info_row['locked'] == 1 && !in_array( $ircdata->host, self::$config['admin_hosts'] ) )
					{
						$reply = array_rand( self::$config['info_locked'] );
						$reply = self::$config['info_locked'][$reply];
						$reply = str_replace( '{nick}', $ircdata->nick, $reply );
						// find a random reply, and replace {shit} with shit, etc. lol

						self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
						// msg to the channel.
						return false;
					}
					// is it locked? :/

					$value = mysql_real_escape_string( trim( $xbot->get_data_after( $keys, 2 ) ) );
					$setby = mysql_real_escape_string( $ircdata->nick );
					$date = time();
					// bleh.. escape all the stuff we're about to insert into the database.

					mysql_query( "DELETE FROM `".self::$config['mysql']['table']."` WHERE `key` = '".$key."'" );
					mysql_query( "INSERT INTO `".self::$config['mysql']['table']."` (`key`,`value`,`setby`,`date`) VALUES('".$key."','".$value."','".$setby."','".$date."')" );
					// update

					$reply = array_rand( self::$config['info_confirm'] );
					$reply = self::$config['info_confirm'][$reply];
					$reply = str_replace( '{nick}', $ircdata->nick, $reply );
					// find a random reply, and replace {shit} with shit, etc. lol

					self::msg( $xbot, $ircdata->from, $ircdata->target, $reply );
					// msg to the channel.
					return false;
				}
				// yes we have
			}
			// no Samantha, * is * (reassignment)
		}
		// only trigger on channel privmsgs		
	}
	// define a main routine function
	// this can be in a class.

	/*
	* format_time (private)
	*
	* @params
	* $seconds - Number of seconds to format into days, hours, mins & seconds
	*/
	static public function format_time( $seconds )
	{
		// haha, this was epically messy, now it's epically
		// sexy.. nah, it does the job i reckon.

		$return = '';

		$days = floor( $seconds / 86400 );
		$remaining = $seconds - ( $days * 86400 );
		$return .= $days.' days, ';
		// days

		$hours = floor( $remaining / 3600 );
		$remaining = $remaining - ( $hours * 3600 );
		$return .= $hours.' hours, ';
		// hours

		$mins = floor( $remaining / 60 );
		$remaining = $remaining - ( $mins * 60 );
		$return .= $mins.' minutes, and ';
		// minutes

		$return .= $remaining.' seconds';
		// seconds

		return $return;
		// return the result.
	}

   /*
	* msg
	*
	* @params
	*/
	static public function msg( $xbot, $from, $target, $reply )
	{
		$return = (object) array(
			'type' => 'privmsg',
			'nick' => self::$config['networks'][$from]['nick'],
			'target' => $target,
			'message' => $reply,
		);

		self::log_chans( $return );
		$xbot->msg( $from, $target, $reply );
	}
}

// EOF;
