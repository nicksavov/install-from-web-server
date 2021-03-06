<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * This models supports retrieving lists of contact categories.
 *
 * @package     Joomla.Site
 * @subpackage  com_apps
 * @since       1.6
 */
class AppsModelExtension extends JModelList
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	public $_context = 'com_apps.extension';

	/**
	 * The category context (allows other extensions to derived from this model).
	 *
	 * @var		string
	 */
	protected $_extension = 'com_apps';

	private $_parent = null;

	private $_items = null;
	
	private $_remotedb = null;
	
	public $_extensionstitle = null;
	
	private $_categories = null;
	
	private $_breadcrumbs = array();

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();
		$this->setState('filter.extension', $this->_extension);

		// Get the parent id if defined.
		$parentId = $app->input->getInt('id');
		$this->setState('filter.parentId', $parentId);

		$params = $app->getParams();
		$this->setState('params', $params);

		$this->setState('filter.published',	1);
		$this->setState('filter.access',	true);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id	A prefix for the store id.
	 *
	 * @return  string  A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.extension');
		$id	.= ':'.$this->getState('filter.published');
		$id	.= ':'.$this->getState('filter.access');
		$id	.= ':'.$this->getState('filter.parentId');

		return parent::getStoreId($id);
	}

	/**
	 * redefine the function an add some properties to make the styling more easy
	 *
	 * @return mixed An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		if (!count($this->_items))
		{
			$app = JFactory::getApplication();
			$menu = $app->getMenu();
			$active = $menu->getActive();
			$params = new JRegistry;
			if ($active)
			{
				$params->loadString($active->params);
			}
			$options = array();
			$options['countItems'] = $params->get('show_cat_items_cat', 1) || !$params->get('show_empty_categories_cat', 0);
			$categories = JCategories::getInstance('Contact', $options);
			$this->_parent = $categories->get($this->getState('filter.parentId', 'root'));
			if (is_object($this->_parent))
			{
				$this->_items = $this->_parent->getChildren();
			} else {
				$this->_items = false;
			}
		}

		return $this->_items;
	}

	public function getParent()
	{
		if (!is_object($this->_parent))
		{
			$this->getItems();
		}
		return $this->_parent;
	}

	private function getRemoteDB() {
		$base_model = $this->getBaseModel();
		return $base_model->getRemoteDB();
	}
	
	private function getBaseModel()
	{
		$base_model = JModelLegacy::getInstance('Base', 'AppsModel');
		return $base_model;
	}
	
	private function getCatID()
	{
		$input = new JInput;
		$id = $input->get('id', null, 'int');

		// Get remote database
		$db = $this->getRemoteDB();
		
		// Get category id
		$query = $db->getQuery(true);
		$query->select('cat_id');
		$query->from('jos_mt_cl');
		$query->where('link_id = ' . (int)$id);
		$db->setQuery($query);
		return $db->loadResult();
	}
	
	public function getCategories()
	{
		$base_model = $this->getBaseModel();
		return $base_model->getCategories($this->getCatID());
	}

	public function getBreadcrumbs()
	{
		$base_model = $this->getBaseModel();
		return $base_model->getBreadcrumbs($this->getCatID());
	}
	
	public function getPluginUpToDate()
	{
		$base_model = $this->getBaseModel();
		return $base_model->getPluginUpToDate();
	}
	
	public function getExtension()
	{
		// Get extension id
		$input = new JInput;
		$id = $input->get('id', null, 'int');
		$release = preg_replace('/[^\d]/', '', base64_decode($input->get('release', '', 'base64')));
		$release = intval($release / 5) * 5;
		
		// Get remote database
		$db = $this->getRemoteDB();
		
		$query = 'SET SESSION group_concat_max_len=150000';
		$db->setQuery($query);
		$db->execute();
		
		// Form query
		$query = $db->getQuery(true);
		$query->select(
			array(
				't2.*',
				't3.filename AS image',
				't6.cat_name AS cat_name',
				't6.cat_id AS cat_id',
				'CONCAT("{", GROUP_CONCAT(DISTINCT "\"", t5.cf_id, "\":\"", t4.value, "\""), "}") AS options',
				'COUNT(DISTINCT t7.rev_id) AS reviews',
			)
		);


		$query->from('jos_mt_links AS t2');
		$query->join('LEFT', 'jos_mt_cl AS t1 ON t1.link_id = t2.link_id');
		$query->join('LEFT', 'jos_mt_images AS t3 ON t3.link_id = t2.link_id');
		$query->join('LEFT', 'jos_mt_cfvalues AS t4 ON t2.link_id = t4.link_id');
		$query->join('LEFT', 'jos_mt_customfields AS t5 ON t4.cf_id = t5.cf_id');
		$query->join('LEFT', 'jos_mt_cats AS t6 ON t6.cat_id = t1.cat_id');
		$query->join('LEFT', 'jos_mt_reviews AS t7 ON t7.link_id = t2.link_id');
		$query->join('RIGHT', 'jos_mt_cfvalues AS t8 ON t8.link_id = t2.link_id AND t8.cf_id = 37 AND ("'.$release.'" REGEXP t8.value OR t8.value = "")');

		$where = array(
			't2.link_id = ' . (int)$id,
			't2.link_published = 1',
			't2.link_approved = 1',
			'(t2.publish_up <= NOW() OR t2.publish_up = "0000-00-00 00:00:00")',
			'(t2.publish_down >= NOW() OR t2.publish_down = "0000-00-00 00:00:00")',
		);

		$query->where($where);
		$query->group('t2.link_id');
		$db->setQuery($query);
		$item = $db->loadObject();

		// Get CDN URL
		$componentParams = JComponentHelper::getParams('com_apps');
		$cdn = preg_replace('#/$#', '', trim($componentParams->get('cdn'))) . "/";
		
		// Create item
		$options = new JRegistry($item->options);
		$item->image = $this->getBaseModel()->getMainImageUrl($item->image);
		$item->fields = $options;
		$item->downloadurl = $options->get($componentParams->get('fieldid_download_url'));
		if (preg_match('/\.xml\s*$/', $item->downloadurl)) {
			$app = JFactory::getApplication();
			$product = addslashes(base64_decode($app->input->get('product', '', 'base64')));
			$release = preg_replace('/[^\d\.]/', '', base64_decode($app->input->get('release', '', 'base64')));
			$dev_level = (int) base64_decode($app->input->get('dev_level', '', 'base64'));

			$updatefile = JPATH_ROOT . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'joomla' . DIRECTORY_SEPARATOR . 'updater' . DIRECTORY_SEPARATOR . 'update.php';
			$fh = fopen($updatefile, 'r');
			$theData = fread($fh, filesize($updatefile));
			fclose($fh);

			$theData = str_replace('<?php', '', $theData);
			$theData = str_replace('$ver->PRODUCT', "'".$product."'", $theData);
			$theData = str_replace('$ver->RELEASE', "'".$release."'", $theData);
			$theData = str_replace('$ver->DEV_LEVEL', "'".$dev_level."'", $theData);
			
			eval($theData);

			$update = new JUpdate;
			$update->loadFromXML($item->downloadurl);
			$package_url_node = $update->get('downloadurl', false);
			if (isset($package_url_node->_data)) {
				$item->downloadurl = $package_url_node->_data;
 			}
		}
		$item->type = $this->getTypeEnum($options->get($componentParams->get('fieldid_download_type')));
		
		return array($item);
		
	}
	
	private function getTypeEnum($text) {
		$options = array();
		$options[0] = 'None';
		$options[1] = 'Free Direct Download link:';
		$options[2] = 'Free but Registration required at link:';
		$options[3] = 'Commercial purchase required at link:';
		
		return array_search($text, $options);
	}

}
