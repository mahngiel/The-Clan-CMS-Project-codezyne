<?php
/**
 * Clan CMS
 *
 * An open source application for gaming clans
 *
 * @package		Clan CMS
 * @author		Xcel Gaming Development Team
 * @copyright	Copyright (c) 2010 - 2011, Xcel Gaming, Inc.
 * @license		http://www.xcelgaming.com/about/license/
 * @link		http://www.xcelgaming.com
 * @since		Version 0.5.0
 */

// ------------------------------------------------------------------------

/**
 * Clan CMS Shouts Controller
 *
 * @package		Clan CMS
 * @subpackage	Controllers
 * @category		Controllers
 * @author		co[dezyne]
 * @link			http://www.codezyne.me
 */
class Shouts extends CI_Controller {
	
	/**
	 * Constructor
	 *
	 */	
	function __construct()
	{
		// Call the Controller constructor
		parent::__construct();
		
		// Load the Shoutbox widget Model
		$this->load->model( 'Shouts_model', 'shouts');
		
		
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * Index
	 *
	 * Display's the articles
	 *
	 * @access	public
	 * @return	void
	 */
	function index()
	{	
		// Check to see if the user is logged in
		if (!$this->user->logged_in())
		{
			// User is not logged in, redirect them
			redirect('account/login');
		}
		
		// Retrieve the page
		$page = $this->uri->segment('3');
	
		// Check if page exists
		if($page == '')
		{
			// Page doesn't exist, assign it
			$page = 1;
		}
	
		//Set up the variables
		$per_page = 40;
		$total_results = $this->shouts->count_shouts();
		$offset = ($page - 1) * $per_page;
		$pages->total_pages = 0;
		
		// Create the pages
		for($i = 1; $i < ($total_results / $per_page) + 1; $i++)
		{
			// Itterate pages
			$pages->total_pages++;
		}
				
		// Check if there are no results
		if($total_results == 0)
		{
			// Assign total pages
			$pages->total_pages = 1;
		}
		
		// Set up pages
		$pages->current_page = $page;
		$pages->pages_left = 9;
		$pages->first = (bool) ($pages->current_page > 40);
		$pages->previous = (bool) ($pages->current_page > '1');
		$pages->next = (bool) ($pages->current_page != $pages->total_pages);
		$pages->before = array();
		$pages->after = array();
		
		// Check if the current page is towards the end
		if(($pages->current_page + 40) < $pages->total_pages)
		{
			// Current page is not towards the end, assign start
			$start = $pages->current_page - 39;
		}
		else
		{
			// Current page is towards the end, assign start
			$start = $pages->current_page - $pages->pages_left + ($pages->total_pages - $pages->current_page);
		}
		
		// Assign end
		$end = $pages->current_page + 1;
		
		// Loop through pages before the current page
		for($page = $start; ($page < $pages->current_page); $page++)
		{ 
			// Check if the page is vaild
			if($page > 0)
			{
				// Page is valid, add it the pages before, increment pages left
				$pages->before = array_merge($pages->before, array($page));
				$pages->pages_left--;
			}
		}
		
		// Loop through pages after the current page
		for($page = $end; ($pages->pages_left > 0 && $page <= $pages->total_pages); $page++)
		{
			// Add the page to pages after, increment pages left
			$pages->after = array_merge($pages->after, array($page));
			$pages->pages_left--;
		}
		
		// Set up pages
		$pages->last = (bool) (($pages->total_pages - 40) > $pages->current_page);
		
		
		// Display previous shouts
		$shouts = $this->shouts->get_shouts($per_page, $offset);
		
		if($shouts)
		{
			// Time Conversions
			$current = strtotime(date('Y-m-d G:i:s'));
			foreach($shouts as $shout)
			{ 
				$delay = round((($current - strtotime($shout->when)) / 60 ), 0);
				
				if($delay <= 3){
					// Shouts less than 3 minutes
					$shout->delay  = 'just now';
				}elseif($delay > 3 && $delay < 60){
					
					// Shouts 3 to 60 minutes
					$shout->delay = $delay . 'mins ago';
					
				}elseif($delay >= 60 && $delay < 1440){
					
					// Shouts greater than one hour 
					$delay = round(($delay / 60), 0);
					$shout->delay  = $delay . ' hours ago';
					
				}elseif($delay >= 1440 && $delay < 10800){
					
					// Shouts greater than a day
					$delay = round(($delay / 1440), 0);
					$shout->delay = $delay . ' days ago';
					
				}elseif($delay >= 10800){
					
					// Shouts greater than a week
					$delay = round(($delay / 10080), 0);
					$shout->delay  = $delay . ' weeks ago';		
												
				}
			}
			
			// Search shouts for hyperlinks
			foreach($shouts as $shout)
			{
				// Break apart the shoutted comment
				$chunk = explode(' ', $shout->shout);
				
				// Iterate through array searching for URIs
				 foreach($chunk as &$bit)
				 {
				 	
				 	$check_www = preg_match('/^www/', $bit);
				 	$check_http = preg_match('/^http/', $bit);
				 	
				 	// If shout contains a link, anchor it, else leave it alone
				 	if($check_www == 1)
				 	{
				 		$bit = '<a href="http://' . $bit . '" target="_blank" />' . $bit . '</a>';
				 	}elseif($check_http == 1)
				 	{
				 		$bit = '<a href="' . $bit . '" target="_blank" />' . $bit . '</a>';
				 	}else{
				 		$bit = $bit;
				 	}
				 	
				 }
				 
				 // Put array back together
				$merged = implode(' ', $chunk);
				
				// Reference newly build shout
				$shout ->shout = $merged;
				
				// Replace whitespace, if exists
				$shout->user_clean = preg_replace('/\s/ ', '+', $shout->user);
			}
		}

		// Create a reference to articles & pages
		$this->data->shouts =& $shouts;
		$this->data->pages =& $pages;
	
		// Load the articles view
		$this->load->view(THEME . 'shouts', $this->data);
	}
	
	// --------------------------------------------------------------------
	/**
	 * Delete Shout
	 *
	 *  Removes shouts
	 *
	 * @access	public
	 * @param	array
	 * @return	array
	 */	
	function del_shout()
	{
		// Set up the data
		$data = array(
			'id'	=>	$this->uri->segment(3, '')
		);

		// Retrieve the header by file_name
		if(!$shout = $this->shouts->get_shout($data))
		{
			// Comment doesn't exist, alert the administrator
			$this->session->set_flashdata('message', 'The shout was not found!');
		
			// Redirect the administrator
			redirect($this->session->userdata('previous'));
		}

		// Delete the article comment from the database
		$this->shouts->delete_shout($shout->id, $data);
		
		// Alert the administrator
		$this->session->set_flashdata('message', 'The shout was successfully deleted!');
				
		// Redirect the administrator
		redirect('shouts/');
	}
	
}

/* End of file shouts.php */
/* Location: ./clancms/controllers/shouts.php */