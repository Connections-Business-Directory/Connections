<?php
class cnUser
{
	/**
	 * @TODO: Initialize the cnUser class with the current user ID and
	 * add a method to make a single call to get_user_meta() rather than
	 * making multiples calls to reduce db accesses.
	 */
	
	/**
	 * Interger: stores the current WP user ID
	 * @var interger
	 */
	private $ID;
	
	/**
	 * String: holds the last set entry type for the persistant filter
	 * @var string
	 */
	private $filterEntryType;
	
	/**
	 * String: holds the last set visibility type for the persistant filter
	 * @var string
	 */
	private $filterVisibility;
	
	public function getID()
    {
        return $this->ID;
    }
    
	public function setID($id)
	{
		$this->ID = $id;
	}
	
	public function getFilterEntryType()
    {
		/*
		 * Use get_user_meta() used in WP 3.0 and newer
		 * since get_usermeta() was deprecated.
		 */
		if ( function_exists('get_user_meta') )
		{
			$user_meta = get_user_meta($this->ID, 'connections', TRUE);
		}
		else
		{
			$user_meta = get_usermeta($this->ID, 'connections');
		}
		
		if ( !$user_meta == NULL && isset($user_meta['filter']['entry_type']) )
		{
			return $user_meta['filter']['entry_type'];
		}
		else
		{
			return 'all';
		}
    }
    
    public function setFilterEntryType($entryType)
    {
		$permittedEntryTypes = array('all', 'individual', 'organization', 'family');
		$entryType = esc_attr($entryType);
		
		if (!in_array($entryType, $permittedEntryTypes)) return FALSE;
		
		/*
		 * Use get_user_meta() used in WP 3.0 and newer
		 * since get_usermeta() was deprecated.
		 */
		if ( function_exists('get_user_meta') )
		{
			$user_meta = get_user_meta($this->ID, 'connections', TRUE);
		}
		else
		{
			$user_meta = get_usermeta($this->ID, 'connections');
		}
		
		$user_meta['filter']['entry_type'] = $entryType;
		
		/*
		 * Use update_user_meta() used in WP 3.0 and newer
		 * since update_usermeta() was deprecated.
		 */
		if ( function_exists('update_user_meta') )
		{
			update_user_meta($this->ID, 'connections', $user_meta);
		}
		else
		{
			update_usermeta($this->ID, 'connections', $user_meta);
		}
		
		// Reset the current user's admin manage page.
		//$this->resetFilterPage();
    }
	
	/**
	 * Returns the cached visibility filter setting as string or FALSE depending if the current user has sufficient permission.
	 * 
	 * @return string || bool
	 */
	public function getFilterVisibility()
    {
        /*
		 * Use get_user_meta() used in WP 3.0 and newer
		 * since get_usermeta() was deprecated.
		 */
		if ( function_exists('get_user_meta') )
		{
			$user_meta = get_user_meta($this->ID, 'connections', TRUE);
		}
		else
		{
			$user_meta = get_usermeta($this->ID, 'connections');
		}
		
		if ( !$user_meta == NULL && isset($user_meta['filter']['visibility']) )
		{
			/*
			 * Reset the user's cached visibility filter if they no longer have access.
			 */
			switch ($user_meta['filter']['visibility'])
			{
				case 'public':
					if (!current_user_can('connections_view_public'))
					{
						return FALSE;
					}
					else
					{
						return $user_meta['filter']['visibility'];
					}
				break;
				
				case 'private':
					if (!current_user_can('connections_view_private'))
					{
						return FALSE;
					}
					else
					{
						return $user_meta['filter']['visibility'];
					}
				break;
				
				case 'unlisted':
					if (!current_user_can('connections_view_unlisted'))
					{
						return FALSE;
					}
					else
					{
						return $user_meta['filter']['visibility'];
					}
				break;
				
				default:
					return FALSE;
				break;
			}
		}
		else
		{
			return FALSE;
		}
    }
    
    public function setFilterVisibility($visibility)
    {
		$permittedVisibility = array('all', 'public', 'private', 'unlisted');
		$visibility = esc_attr($visibility);
		
		if (!in_array($visibility, $permittedVisibility)) return FALSE;
		
		/*
		 * Use get_user_meta() used in WP 3.0 and newer
		 * since get_usermeta() was deprecated.
		 */
		if ( function_exists('get_user_meta') )
		{
			$user_meta = get_user_meta($this->ID, 'connections', TRUE);
		}
		else
		{
			$user_meta = get_usermeta($this->ID, 'connections');
		}
		
		$user_meta['filter']['visibility'] = $visibility;
		
		/*
		 * Use update_user_meta() used in WP 3.0 and newer
		 * since update_usermeta() was deprecated.
		 */
		if ( function_exists('update_user_meta') )
		{
			update_user_meta($this->ID, 'connections', $user_meta);
		}
		else
		{
			update_usermeta($this->ID, 'connections', $user_meta);
		}
		
		// Reset the current user's admin manage page.
		//$this->resetFilterPage();
    }
	
	
	/**
	 * Returns the current set filter to be used to display the entries.
	 * The default is to return only the approved entries if not set.
	 * 
	 * @return string
	 */
	public function getFilterStatus()
    {
		// Set the moderation filter for the current user if set in the query string.
		if ( isset($_GET['status']) ) $this->setFilterStatus( $_GET['status'] );
		
		/*
		 * Use get_user_meta() used in WP 3.0 and newer
		 * since get_usermeta() was deprecated.
		 */
		if ( function_exists('get_user_meta') )
		{
			$user_meta = get_user_meta($this->ID, 'connections', TRUE);
		}
		else
		{
			$user_meta = get_usermeta($this->ID, 'connections');
		}
		
		if ( !$user_meta == NULL && isset($user_meta['filter']['status']) )
		{
			return $user_meta['filter']['status'];
		}
		else
		{
			return 'approved';
		}
    }
	
	public function setFilterStatus($status)
    {
		$permittedVisibility = array('all', 'approved', 'pending');
		$status = esc_attr($status);
		
		if (!in_array($status, $permittedVisibility)) return FALSE;
		
		/*
		 * Use get_user_meta() used in WP 3.0 and newer
		 * since get_usermeta() was deprecated.
		 */
		if ( function_exists('get_user_meta') )
		{
			$user_meta = get_user_meta($this->ID, 'connections', TRUE);
		}
		else
		{
			$user_meta = get_usermeta($this->ID, 'connections');
		}
		
		$user_meta['filter']['status'] = $status;
		
		/*
		 * Use update_user_meta() used in WP 3.0 and newer
		 * since update_usermeta() was deprecated.
		 */
		if ( function_exists('update_user_meta') )
		{
			update_user_meta($this->ID, 'connections', $user_meta);
		}
		else
		{
			update_usermeta($this->ID, 'connections', $user_meta);
		}
		
		// Reset the current user's admin manage page.
		//$this->resetFilterPage();
    }
	
	public function getFilterCategory()
    {
        /*
		 * Use get_user_meta() used in WP 3.0 and newer
		 * since get_usermeta() was deprecated.
		 */
		if ( function_exists('get_user_meta') )
		{
			$user_meta = get_user_meta($this->ID, 'connections', TRUE);
		}
		else
		{
			$user_meta = get_usermeta($this->ID, 'connections');
		}
		
		if ( !$user_meta == NULL && isset($user_meta['filter']) )
		{
			return $user_meta['filter']['category'];
		}
		else
		{
			return '';
		}
    }
    
    public function setFilterCategory($id)
    {
        // If value is -1 from drop down, set to NULL
		if ($id == -1) $id = NULL;
		
		/*
		 * Use get_user_meta() used in WP 3.0 and newer
		 * since get_usermeta() was deprecated.
		 */
		if ( function_exists('get_user_meta') )
		{
			$user_meta = get_user_meta($this->ID, 'connections', TRUE);
		}
		else
		{
			$user_meta = get_usermeta($this->ID, 'connections');
		}
		
		$user_meta['filter']['category'] = $id;
		
		/*
		 * Use update_user_meta() used in WP 3.0 and newer
		 * since update_usermeta() was deprecated.
		 */
		if ( function_exists('update_user_meta') )
		{
			update_user_meta($this->ID, 'connections', $user_meta);
		}
		else
		{
			update_usermeta($this->ID, 'connections', $user_meta);
		}
		
		// Reset the current user's admin manage page.
		//$this->resetFilterPage();
    }
	
	/**
	 * Returns the current page and page limit of the supplied page name.
	 * 
	 * @param string $page
	 * @return object
	 */
	public function getFilterPage( $pageName )
    {
		$user_meta = get_user_meta($this->ID, 'connections', TRUE);
		
		if ( ! $user_meta == NULL && isset($user_meta['filter'][$pageName]) )
		{
			$page = (object) $user_meta['filter'][$pageName];
			
			if ( ! isset($page->limit) || empty($page->limit) ) $page->limit = 50;
			if ( ! isset($page->current) || empty($page->current) ) $page->current = 1;
			
			return $page;
		}
		else
		{
			$page = new stdClass();
			
			$page->limit = 50;
			$page->current = 1;
			
			return $page;
		}
    }
	
	/**
	 *@param object $page
	 */
	public function setFilterPage( $page )
    {
		// If the page name has not been supplied, no need to process further.
		if ( ! isset($page->name) ) return;
		
		$page->name = sanitize_title($page->name);
		
		if ( isset($page->current) ) $page->current = absint($page->current);
		if ( isset($page->limit) ) $page->limit = absint($page->limit);
		
		$user_meta = get_user_meta($this->ID, 'connections', TRUE);
		
		if ( isset($page->current) ) $user_meta['filter'][$page->name]['current'] = $page->current;
		if ( isset($page->limit) ) $user_meta['filter'][$page->name]['limit'] = $page->limit;
		
		update_user_meta($this->ID, 'connections', $user_meta);
    }
	
	public function resetFilterPage( $pageName )
	{
		$page = $this->getFilterPage($pageName);
		
		$page->name = $pageName;
		$page->current = 1;
		
		$this->setFilterPage($page);
	}
	
	public function setMessage($message)
	{
		$user_meta = get_user_meta($this->ID, 'connections', TRUE);
		
		$user_meta['messages'][] = $message;
		
		update_user_meta($this->ID, 'connections', $user_meta);
	}
	
	public function getMessages()
	{
		$user_meta = get_user_meta($this->ID, 'connections', TRUE);
		
		if ( ! empty($user_meta['messages']) )
		{
			return $user_meta['messages'];
		}
		else
		{
			return array();
		}
	}
	
	public function resetMessages()
	{
		$user_meta = get_user_meta($this->ID, 'connections', TRUE);
		
		if ( isset($user_meta['messages']) )unset($user_meta['messages']);
		
		update_user_meta($this->ID, 'connections', $user_meta);
	}
}
?>