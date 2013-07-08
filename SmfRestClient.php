<?php

/**
 * Simple Machines Forum(SMF) 'REST' API for SMF 2.0
 *
 * Use this to integrate your SMF version 2.0 forum with 3rd party software
 * If you need help using this script or integrating your forum with other
 * software, feel free to contact andre@r2bconcepts.com
 *
 * @package   SMF 2.0 'REST' API
 * @author    Simple Machines http://www.simplemachines.org
 * @author    Andre Nickatina <andre@r2bconcepts.com>
 * @copyright 2011 Simple Machines
 * @link      http://www.simplemachines.org Simple Machines
 * @link      http://www.r2bconcepts.com Red2Black Concepts
 * @license   http://www.simplemachines.org/about/smf/license.php BSD
 * @version   0.1.0
 *
 * NOTICE OF LICENSE
 ***********************************************************************************
 * This file, and ONLY this file is released under the terms of the BSD License.   *
 *                                                                                 *
 * Redistribution and use in source and binary forms, with or without              *
 * modification, are permitted provided that the following conditions are met:     *
 *                                                                                 *
 * Redistributions of source code must retain the above copyright notice, this     *
 * list of conditions and the following disclaimer.                                *
 * Redistributions in binary form must reproduce the above copyright notice, this  *
 * list of conditions and the following disclaimer in the documentation and/or     *
 * other materials provided with the distribution.                                 *
 * Neither the name of Simple Machines LLC nor the names of its contributors may   *
 * be used to endorse or promote products derived from this software without       *
 * specific prior written permission.                                              *
 *                                                                                 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"     *
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE       *
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE      *
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE        *
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR             *
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE *
 * GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)     *
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT      *
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT   *
 * OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE. *
 **********************************************************************************/

/*
    
 	
*/

define ('API_SERVER', 'http://www.yourdomain.com/path/to/api/'); //this will be the path to your api folder
define ('API_DEBUG', false); //show the $_REQUEST sent by cURL

if (!function_exists('curl_init'))  throw new Exception('SMF API Client Library requires the cURL PHP extension.');
if (!function_exists('json_decode')) throw new Exception('SMF API Client Library requires the JSON PHP extension.');

class SmfRestClient
{
    private $secretKey;
    private $format;
    private $sessionId;
    private $cookieFile;
    private $userAuth = array();

    /**
     * Construct magic method
     */
    public function __construct($secretKey = null, $format = 'json')
    {
        $this->secretKey = $secretKey;
        $this->format    = $format;

        // when not requesting raw data lets automatically use json, for easier decoding to object
        if ('json' != $this->format) {
            $this->format = 'raw';
        }
    }
    
    /**
     * Destruct magic method
     *
     * We're using this to check for any unused 'cookies' and delete them
     */
    public function __destruct()
    {
        foreach (glob("$this->save_path/sess_*") as $filename) {
            if (filemtime($filename) + 3600 < time()) {
                @unlink($filename);
            }
        }
    }
	
    /**
     * Call method
     *
     * Puts the necessary variables together for the function call and sends them to be cURL'd
     *
     * @param  string $method the name of the method that will be called
     * @param  array $params an array of the params that will be used in the function
     * @return array or object  containing the methods output
     */
    protected function call_method($method, $params = array())
    {
    	$authParams = array();

        if (!empty($this->secretKey)) {
    	    $authParams['secretKey'] = $this->secretKey;
        }

    	// keep the secret key first
    	$params = array_merge($authParams, $params);

    	$request = "$method.$this->format";

    	return $this->post_request($request, $params);
    }

    /**
     * Post request
     *
     * Call the method via cURL and return the results
     *
     * @param  string $request the method to call and format to return results in
     * @param  array  $params the function parameters
     * @return array or object depending on format
     */
    protected function post_request($request, $params)
    {
        if ('' == session_id()) {
             session_start();
        }

        // build the 'cookie' filename and path
        $this->save_path  = dirname(__FILE__) . '/session';
        $this->sessionId  = session_id();
        $cookieFile       = $this->save_path . '/sess_' . $this->sessionId . '.txt';
        $this->cookieFile = $cookieFile;
    
        $url = API_SERVER . "$request";

        if (API_DEBUG) {
            echo "REQUEST: $url?" . http_build_query($params);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $rawData = curl_exec($ch);
        curl_close($ch);

        if ('raw' == $this->format) {
            return $rawData;
        } else {
            return $this->toObject($rawData);
        }
    }

    /**
     * To object
     *
     * Decode data to object
     *
     * @param  string $rawdata the data to be decoded
     * @return object $result the decoded object
     */
    protected function toObject($rawData)
    {
        $result = null;

        if (!empty($rawData)) {
            $result = json_decode($rawData);
        }

        return $result;
    }
    
    // ***************
    // Special Methods
    // ***************
    
    /**
     * Logout user from API
     *
     * Logs the user out if they were logged in using this API.
     */
    public function logout_userRest()
    {
        return $this->call_method("logout/userRest",
            array()
        );
    }
    
    /**
     * Create post
     *
     * Post to the forum. See Sources/Subs-Post.php for an idea of the options you can use
     *
     * @param  array $msgOptions message options
     * @param  array $topicOptions topic options
     * @param  array $posterOptions poster options
     * @return bool whether the post was created successfully
     */
    public function create_post($msgOptions = array(), $topicOptions = array(), $posterOptions = array())
    {
        return $this->call_method("create/post",
            array('msgOptions'    => serialize($msgOptions),
                  'topicOptions'  => serialize($topicOptions),
                  'posterOptions' => serialize($posterOptions),
            )
        );
    }
    
    /**
     * Send PM
     *
     * Sends a personal message to a member or members
     *
     * @param  array $recipients an array containg the arrays 'to' and 'int' which are arrays containing the int
     *    // member id's of the members to send the pm to. $recipients will be structured like this:
     *    // $recipients = array('to' => array(), 'bcc' => array());
     * @param  string $subject the message subject
     * @param  string $message the message
     * @param  bool $store_outbox
     * @param  array $from variables that can be set regarding message sender. Leave null to use logged user
     * @param  int $pm_head
     * @return array number of pm's sent successfully and unsuccessfully
     */
    
    public function send_pm($recipients, $subject, $message, $store_outbox = false, $from = null, $pm_head = 0)
    {
        return $this->call_method("send/pm",
            array('recipients'   => serialize($recipients),
                  'subject'      => $subject,
                  'message'      => $message,
                  'store_outbox' => $store_outbox,
                  'from'         => serialize($from),
                  'pm_head'      => $pm_head,
            )
        );
    }

    // ***************
    // API Methods
    // ***************

    /**
     * Get user
     *
     * Get the info of a user given their id, email or username
     *
     * @param  int||string $identifier the member id, email or username
     * @return array the users information from the db
     */
    public function get_user($identifier)
    {
        return $this->call_method("get/user",
            array('identifier' => $identifier,
            )
        );
    }
    
    /**
     * Get user info
     *
     * Gets user info about the currently logged in user. Will return only the $user_info array,
     * call get_user() on the id to get ALL the user's info
     *
     * @return array containing the $user_info for current user
     */
    public function get_userInfo()
    {
        return $this->call_method("get/userInfo",
            array()
        );
    }
    
    /**
     * Login user
     *
     * Log a user in given their id, email or username. Does NO checking, call authenticate_user if
     * you want to check the login. Will only log them in remotely as cookies cannot be set cross-domain
     *
     * @param  int||string $identifier member id, email or username
     * @param  int $cookieLength the length to set the cookie for in seconds
     * @return bool whether login was successfull or not
     */
    public function login_user($identifier='', $cookieLength=525600)
    {
        return $this->call_method("login/user",
            array('identifier'   => $identifier,
                  'cookieLength' => $cookieLength,
            )
        );
    }

    /**
     * Authenticate user login
     *
     * Checks the username password combo for validity
     *
     * @param  int||string $username the member id, email or username
     * @param  string $password plain or encrypted in a variety of ways
     * @param  bool whether the password is encrypted (if not sure leave empty, it won't matter)
     * @return bool whether the login is valid or not
     */
    public function authenticate_user($username = '', $password = '', $encrypted = true)
    {
        return $this->call_method("authenticate/user",
            array('username'  => $username,
                  'password'  => $password,
                  'encrypted' => $encrypted,
            )
        );
    }

    /**
     * Worthless
     *
     * You cannot touch cookies on the local SMF server so this ain't gonna work
     *
     * @param
     * @return false
     */
    public function logout_user($username = '')
    {
        return $this->call_method("logout/user",
            array('username' => $username,
            )
        );
    }

    /**
     * Delete member(s)
     *
     * Delete a member or members
     *
     * @param  int||array $users member id or array of member id's to delete
     * @return bool whether the member(s) are gone 
     */
    public function delete_members($users)
    {
        return $this->call_method("delete/members",
            array('users' => serialize($users),
            )
        );
    }
    
    /**
     * Register member
     *
     * Register a new member
     *
     * @param  array $regOptions there are lots of options you can use but
     *    //'member_name' (unique), 'email' (unique) and 'password' are required
     * @return int member id || bool false
     */
    public function register_member($regOptions = array())
    {
        return $this->call_method("register/member",
            array('regOptions' => serialize($regOptions),
            )
        );
    }
    
    /**
     * Log error
     *
     * Log an error into the SMF error log
     *
     * @param  string $error_message the message to log
     * @param  string $error_type the type of error
     * @param  string $file the filename
     * @param  string $line the line number
     * @return bool whether logging was successfull 
     */
    public function log_error($error_message, $error_type = 'general', $file = null, $line = null)
    {
        return $this->call_method("log/error",
            array('error_message' => $error_message,
                  'error_type'    => $error_type,
                  'file'          => $file,
                  'line'          => $line,
            )
        );
    }
    
    /**
     * Update member data
     *
     * Update member info in SMF
     *
     * @param  int||string $member the member id, email, or username
     * @param  array $info associative array of data to change
     *    // $info = array('email_address' => $newEmail)
     * @return bool success or not
     */
    public function update_memberData($member = '', $info = '')
    {
        return $this->call_method("update/memberData",
            array('member' => $member,
                  'info'   => serialize($info),
            )
        );
    }
    
    /**
     * Check if user online
     *
     * Check if a specified user is online
     *
     * @param  int||string $identifier the member id, email or username
     * @return bool whether they are online or not
     */
    public function check_ifOnline($identifier)
    {
        return $this->call_method("check/ifOnline",
            array('identifier' => $identifier,
            )
        );
    }
    
    /**
     * Log user online
     *
     * Log a user as being online. There is a similar function in the SSI
     *
     * @param  int||string $identifier member id, email or username
     * @return bool whether they were logged online
     */
    public function log_onlineApi($identifier)
    {
        return $this->call_method("log/onlineApi",
            array('identifier' => $identifier,
            )
        );
    }

    // ***************
    // SSI Methods
    // ***************
    
    /**
     * Shutdown SSI
     *
     * Shows the footer
     */
    public function shutdown_ssi()
    {
        return $this->call_method("shutdown/ssi",
            array()
        );
    }
    
    /**
     * Show welcome
     *
     * Shows the welcome message
     */
    public function show_welcome($output_method = 'echo')
    {
        return $this->call_method("show/welcome",
            array('output_method' => $output_method,
            )
        );
    }

    /**
     * Show menubar
     *
     * Show the SMF menubar
     */
    public function show_menubar($output_method = 'echo')
    {
        return $this->call_method("show/menubar",
            array('output_method' => $output_method,
            )
        );
    }

    /**
     * Worthless
     *
     * The session ID of the logout link won't be correct :p
     */
    public function show_logoutLink($redirect_to = '', $output_method = 'echo')
    {
        return $this->call_method("show/logoutLink",
            array('redirect_to'   => $redirect_to,
                  'output_method' => $output_method,
            )
        );
    }
    
    /**
     * Show recent posts
     *
     * Display specified number of recent posts
     */
    public function show_recentPosts($num_recent = 8, $exclude_boards = null, $include_boards = null, $output_method = 'echo', $limit_body = true)
    {
        return $this->call_method("show/recentPosts",
            array('num_recent'     => $num_recent,
                  'exclude_boards' => serialize($exclude_boards),
                  'include_boards' => serialize($include_boards),
                  'output_method'  => $output_method,
                  'limit_body'     => $limit_body,
            )
        );
    }
    
    /**
     * Fetch posts
     *
     * Fetch specific post(s)
     */
    public function fetch_posts($post_ids, $override_permissions = false, $output_method = 'echo')
    {
        return $this->call_method("fetch/posts",
            array('post_ids'             => serialize($post_ids),
                  'override_permissions' => $override_permisssions,
                  'output_method'        => $output_method,
            )
        );
    }
    
    /**
     * Show recent topics
     *
     * Display the specified number of recent topics
     */
    public function show_recentTopics($num_recent = 8, $exclude_boards = null, $include_boards = null, $output_method = 'echo')
    {
        return $this->call_method("show/recentTopics",
            array('num_recent'     => $num_recent,
                  'exclude_boards' => serialize($exclude_boards),
                  'include_boards' => serialize($include_boards),
                  'output_method'  => $output_method,
            )
        );
    }
    
    /**
     * Show top poster
     *
     * Show the specified number of top poster(s)
     */
    public function show_topPoster($topNumber = 1, $output_method = 'echo')
    {
        return $this->call_method("show/topPoster",
            array('topNumber'     => $topNumber,
                  'output_method' => $output_method,
            )
        );
    }
    
    /**
     * Show top boards
     *
     * Display the specified number of top boards
     */
    public function show_topBoards($num_top = 10, $output_method = 'echo')
    {
        return $this->call_method("show/topBoards",
            array('num_top'       => $num_top,
                  'output_method' => $output_method,
            )
        );
    }
    
    /**
     * Show top topics
     *
     * Display the specified number of top topics
     *
     * @param  string $type will be either 'replies' or 'views'
     */
    public function show_topTopics($type = 'replies', $num_topics = 10, $output_method = 'echo')
    {
        return $this->call_method("show/topTopics",
            array('type'          => $type,
                  'num_topics'    => $num_topics,
                  'output_method' => $output_method,
            )
        );
    }
    
    /**
     * Show latest member
     *
     * Display the latest member info
     */
    public function show_latestMember($output_method = 'echo')
    {
        return $this->call_method("show/latestMember",
            array('output_method' => $output_method,
            )
        );
    }
    
    /**
     * Show random member
     *
     * Display a random member
     *
     * @param string $random_type will be either '' or 'day' for only one per day
     */
    public function show_randomMember($random_type = '', $output_method = 'echo')
    {
        return $this->call_method("show/randomMember",
            array('random_type'   => $random_type,
                  'output_method' => $output_method,
            )
        );
    }
    
    /**
     * Fetch member data
     *
     * Fetch data for specific member(s)
     */
    public function fetch_member($member_ids, $output_method = 'echo')
    {
        return $this->call_method("fetch/member",
            array('member_ids'    => serialize($member_ids),
                  'output_method' => $output_method,
            )
        );
    }
    
    /**
     * Fetch group members
     *
     * Fetch data for members of a particular group
     */
    public function fetch_groupMembers($group_id, $output_method = 'echo')
    {
        return $this->call_method("fetch/groupMembers",
            array('group_id'      => $group_id,
                  'output_method' => $output_method,
            )
        );
    }
    
    /**
     * Query members
     *
     * Execute a db query
     */
    public function query_members($query_where, $query_where_params = array(), $query_limit = '', $query_order = 'id_member DESC', $output_method = 'echo')
    {
        return $this->call_method("query/members",
            array('query_where'        => $query_where,
                  'query_where_params' => serialize($query_where_params),
                  'query_limit'        => $query_limit,
                  'query_order'        => $query_order,
                  'output_method'      => $output_method,
            )
        );
    }
    
    /**
     * Show board stats
     *
     * Display board statistics
     */
    public function show_boardStats($output_method = 'echo')
    {
        return $this->call_method("show/boardStats",
            array('output_method' => $output_method,
            )
        );
    }
    
    /**
     * Show who's online
     *
     * Display list of members online
     */
    public function show_whosOnline($output_method = 'echo')
    {
        return $this->call_method("show/whosOnline",
            array('output_method' => $output_method,
            )
        );
    }
    
    /**
     * Log user online
     *
     * Log a user online
     */
    public function log_online($output_method = 'echo')
    {
        return $this->call_method("log/online",
            array('output_method' => $output_method,
            )
        );
    }
    
    /**
     * Show login box
     *
     * It'll draw the login box correctly, but if used it will log them into SMF locally, to log
     * them in remotely use login_user()
     */
    public function show_loginBox($redirect_to = '', $output_method = 'echo')
    {
        return $this->call_method("show/loginBox",
            array('redirect_to'   => $redirect_to,
                  'output_method' => $output_method,
            )
        );
    }
    
    /**
     * Show recent poll
     *
     * Display the most recent poll
     *
     * @param bool $topPollInstead whether to show the top poll instead of the most recent
     */
    public function show_recentPoll($topPollInstead = false, $output_method = 'echo')
    {
        return $this->call_method("show/recentPoll",
            array('topPollInstead' => $topPollInstead,
                  'output_method'  => $output_method,
            )
        );
    }
    
    /**
     * Show poll
     *
     * Display a specific poll
     */
    public function show_poll($topic = null, $output_method = 'echo')
    {
        return $this->call_method("show/poll",
            array('topic'         => $topic,
                  'output_method' => $output_method,
            )
        );
    }
    
    /**
     * Show quick search
     *
     * Display a quick search box
     */
    public function show_quickSearch($output_method = 'echo')
    {
        return $this->call_method("show/quickSearch",
            array('output_method' => $output_method,
            )
        );
    }
    
    /**
     * Show news
     *
     * Display forum news
     */
    public function show_news($output_method = 'echo')
    {
        return $this->call_method("show/news",
            array('output_method' => $output_method,
            )
        );
    }
    
    /**
     * Show todays birthdays
     */
    public function show_todaysBirthdays($output_method = 'echo')
    {
        return $this->call_method("show/todaysBirthdays",
            array('output_method' => $output_method,
            )
        );
    }
    
    /**
     * Show todays holidays
     */
    public function show_todaysHolidays($output_method = 'echo')
    {
        return $this->call_method("show/todaysHolidays",
            array('output_method' => $output_method,
            )
        );
    }
    
    /**
     * Show todays events
     */
    public function show_todaysEvents($output_method = 'echo')
    {
        return $this->call_method("show/todaysEvents",
            array('output_method' => $output_method,
            )
        );
    }
    
    /**
     * Show todays calendar
     */
    public function show_todaysCalendar($output_method = 'echo')
    {
        return $this->call_method("show/todaysCalendar",
            array('output_method' => $output_method,
            )
        );
    }
    
    /**
     * Show board news
     *
     * Display news from a specific board
     */
    public function show_boardNews($board = null, $limit = null, $start = null, $length = null, $output_method = 'echo')
    {
        return $this->call_method("show/boardNews",
            array('board'         => $board,
                  'limit'         => $limit,
                  'start'         => $start,
                  'length'        => $length,
                  'output_method' => $output_method,
            )
        );
    }
    
    /**
     * Show recent events
     *
     * Display recent forum events from the calendar
     */
    public function show_recentEvents($max_events = 7, $output_method = 'echo')
    {
        return $this->call_method("show/recentEvents",
            array('max_events'    => $max_events,
                  'output_method' => $output_method,
            )
        );
    }
    
    /**
     * Check password
     *
     * Check a login. If you don't have the member id, or a plaintext password, use
     * authenticate_user() instead which will accept more info
     */
    public function check_password($id = null, $password = null, $is_username = false)
    {
        return $this->call_method("check/password",
            array('id'          => $id,
                  'password'    => $password,
                  'is_username' => $is_username,
            )
        );
    }
    
    /**
     * Show recent attachments
     *
     * Display recent attachments
     */
    public function show_recentAttachments($num_attachments = 10, $attachment_ext = array(), $output_method = 'echo')
    {
        return $this->call_method("show/recentAttachments",
            array('num_attachments' => $num_attachments,
                  'attachment_ext'  => serialize($attachment_ext),
                  'output_method'   => $output_method,
            )
        );
    }
}
