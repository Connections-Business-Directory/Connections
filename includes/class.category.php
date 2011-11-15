<?php

class cnCategory
{
	private $id;
	private $name;
	private $slug;
	private $termGroup;
	private $taxonomy;
	private $description;
	private $parent;
	private $count;
	private $children;
	
	function __construct($data = NULL)
	{
		if ( isset($data) )
		{
			if ( isset($data->term_id) ) $this->id = $data->term_id;
			if ( isset($data->name) ) $this->name = $data->name;
			if ( isset($data->slug) ) $this->slug = $data->slug;
			if ( isset($data->term_group) ) $this->termGroup = $data->term_group;
			if ( isset($data->taxonomy) ) $this->taxonomy = $data->taxonomy;
			if ( isset($data->description) ) $this->description = $data->description;
			if ( isset($data->parent) ) $this->parent = $data->parent;
			if ( isset($data->count) ) $this->count = $data->count;
			if ( isset($data->children) ) $this->children = $data->children;
		}
	}
    
    /**
     * Returns $children.
     *
     * @see cnCategory::$children
     */
    public function getChildren() {
        return $this->children;
    }
    
    /**
     * Sets $children.
     *
     * @param object $children
     * @see cnCategory::$children
     */
    public function setChildren($children) {
        $this->children = $children;
    }
    
    /**
     * Returns $count.
     *
     * @see cnCategory::$count
     */
    public function getCount() {
        return $this->count;
    }
    
    /**
     * Sets $count.
     *
     * @param object $count
     * @see cnCategory::$count
     */
    public function setCount($count) {
        $this->count = $count;
    }
    
    /**
     * Returns $description.
     *
     * @see cnCategory::$description
     */
    public function getDescription() {
        return $this->description;
    }
    
    /**
     * Sets $description.
     *
     * @param object $description
     * @see cnCategory::$description
     */
    public function setDescription($description) {
        $this->description = $description;
    }
    
    /**
     * Returns $id.
     *
     * @see cnCategory::$id
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Sets $id.
     *
     * @param object $id
     * @see cnCategory::$id
     */
    public function setId($id) {
        $this->id = $id;
    }
    
    /**
     * Returns $name.
     *
     * @see cnCategory::$name
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Sets $name.
     *
     * @param object $name
     * @see cnCategory::$name
     */
    public function setName($name) {
        $this->name = $name;
    }
    
    /**
     * Returns $parent.
     *
     * @see cnCategory::$parent
     */
    public function getParent() {
        return $this->parent;
    }
    
    /**
     * Sets $parent.
     *
     * @param object $parent
     * @see cnCategory::$parent
     */
    public function setParent($parent) {
        $this->parent = $parent;
    }
    
    /**
     * Returns $slug.
     *
     * @see cnCategory::$slug
     */
    public function getSlug() {
        return $this->slug;
    }
    
    /**
     * Sets $slug.
     *
     * @param object $slug
     * @see cnCategory::$slug
     */
    public function setSlug($slug) {
        $this->slug = $slug;
    }
    
    /**
     * Returns $taxonomy.
     *
     * @see cnCategory::$taxonomy
     */
    public function getTaxonomy() {
        return $this->taxonomy;
    }
    
    /**
     * Sets $taxonomy.
     *
     * @param object $taxonomy
     * @see cnCategory::$taxonomy
     */
    public function setTaxonomy($taxonomy) {
        $this->taxonomy = $taxonomy;
    }
    
    /**
     * Returns $termGroup.
     *
     * @see cnCategory::$termGroup
     */
    public function getTermGroup() {
        return $this->termGroup;
    }
    
    /**
     * Sets $termGroup.
     *
     * @param object $termGroup
     * @see cnCategory::$termGroup
     */
    public function setTermGroup($termGroup) {
        $this->termGroup = $termGroup;
    }
	
	/**
	 * Saves the category to the database via the cnTerm class.
	 * 
	 * @return The success or error message.
	 */
	public function save()
	{
		global $connections;
		
		// If the category already exists, do not let it be created.
		if ( $connections->term->getTermBy('name', $this->name, 'category') ) return $connections->setErrorMessage('category_duplicate_name');
		
		$attributes['slug'] = $this->slug;
		$attributes['description'] = $this->description;
		$attributes['parent'] = $this->parent;
		
		// Do not add the uncategorized category
		if (strtolower($this->name) != 'uncategorized')
		{
			if ($connections->term->addTerm($this->name, 'category', $attributes))
			{
				$connections->setSuccessMessage('category_added');
			}
			else
			{
				$connections->setErrorMessage('category_add_failed');
			}
		}
		else
		{
			$connections->setErrorMessage('category_add_uncategorized');
		}
	}
    
	/**
	 * Updates the category to the database via the cnTerm class.
	 * 
	 * @return The success or error message.
	 */
	public function update()
	{
		global $connections;
		
		$attributes['name'] = $this->name;
		$attributes['slug'] = $this->slug;
		$attributes['parent']= $this->parent;
		$attributes['description'] = $this->description;
		
		// Make sure the category isn't being set to itself as a parent.
		if ($this->id === $this->parent)
		{
			$connections->setErrorMessage('category_self_parent');
			return;
		}
		
		// Do not change the uncategorized category
		if ($this->slug != 'uncategorized')
		{
			if ($connections->term->updateTerm($this->id, 'category', $attributes))
			{
				$connections->setSuccessMessage('category_updated');
			}
			else
			{
				$connections->setErrorMessage('category_update_failed');
			}
		}
		else
		{
			$connections->setErrorMessage('category_update_uncategorized');
		}
	}
	
	/**
	 * Deletes the category from the database via the cnTerm class.
	 * 
	 * @return The success or error message.
	 */
	public function delete()
	{
		global $connections;
		
		// Do not delete the uncategorized category
		if ($this->slug != 'uncategorized')
		{
			if ($connections->term->deleteTerm($this->id, $this->parent, 'category'))
			{
				$connections->setSuccessMessage('category_deleted');
			}
			else
			{
				$connections->setErrorMessage('category_delete_failed');
			}
		}
		else
		{
			$connections->setErrorMessage('category_delete_uncategorized');
		}
	}
}

?>