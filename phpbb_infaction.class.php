<?php

/**
 * sort of an api for the thing
 * all creations or modifications are made this way
 * has a build from template function booyaka
 * 
 */
 
class phpbb_infraction
{
	protected $data = array(); // Fill using [field (from db)] => value, basically run this through phpbb dbal to generate sql
	
	/**
	 * Create an infraction
	 * If $infraction_id is zero, then we are making a new one,
	 * If its an integer, then fill data with tthat infraction
	 * When modifying, the other class will check permisions, etc etc.
	 * We must validate data here though ?
	 * Nah, in the main class.
	 */
	public function __construct($infraction_id = 0)
	{
		
		
		if($infraction_id > 0 && is_numeric($infraction_id))
		{
			// Filling from another infraction
			
			return;
		}
		
		
	}
	
	public function build_from_template($template_id)
	{
		// template id from the template thing
		if(!is_numeric($template_id))
		{
			throw new Exception('Template ID must be numeric')
		}
	}
	
	public function
}
