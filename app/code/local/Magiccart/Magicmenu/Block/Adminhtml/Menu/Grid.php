<?php
/**
 * Magiccart 
 * @category  Magiccart 
 * @copyright   Copyright (c) 2014 Magiccart (http://www.magiccart.net/) 
 * @license   http://www.magiccart.net/license-agreement.html
 * @Author: Magiccart<team.magiccart@gmail.com>
 * @@Create Date: 2014-05-28 10:10:00
 * @@Modify Date: 2014-11-09 20:30:36
 * @@Function:
 */
 ?>
<?php

class Magiccart_Magicmenu_Block_Adminhtml_Menu_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('menuGrid');
		// $this->setDefaultSort('menu_id');
		$this->setUseAjax(true);
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	public function  getStaticBlock()
	{
		// $options = Mage::getResourceModel('cms/block_collection')->load()->toOptionArray(); // if use value is id
		$options = array();
		$blocks = Mage::getResourceModel('cms/block_collection')->load();
		foreach ($blocks as $block) {
			$options[$block->getIdentifier()] = $block->getTitle();
		}
		return $options; 
	}

	public function getCatTop()
	{

		$catTop = Mage::getModel('catalog/category')
			->getCollection()
			->addAttributeToSelect('*')
			->addIsActiveFilter()
			->addLevelFilter(2)
			->addOrderField('name');
		$options = array('value'=>'', 'label'=>Mage::helper('catalog')->__('Please select a category ...'));
		foreach ($catTop as $cat) {
			$options[$cat->getEntityId()] = $cat->getName();
		}
		return $options;
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('magicmenu/menu')->getCollection()
		  					->addFieldToFilter('extra', 0);

		foreach($collection as $link) { // renderer stores
			if($link->getStores() && $link->getStores() != 0 ){
				$link->setStores(explode(',',$link->getStores()));
			}else {
				$link->setStores(array('0'));
			}
		}
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		// $this->addColumn('menu_id', array(
		// 	'header'    => Mage::helper('magicmenu')->__('ID'),
		// 	'align'     =>'right',
		// 	'width'     => '50px',
		// 	'index'     => 'menu_id',
		// ));

		$this->addColumn('cat_id', array(
			'header'    => Mage::helper('magicmenu')->__('Category'),
			'align'     =>'left',
			'index'     => 'cat_id',
			'type'      => 'options',
			'width'     => '150px',
			'options'   => $this->getCatTop(),
		));

		$this->addColumn('top', array(
		    'header'    => Mage::helper('magicmenu')->__('Block Top'),
		    'align'     =>'left',
		    'index'     => 'top',
		    'type'      => 'options',
		    'options'   => $this->getStaticBlock(),
		));

		$this->addColumn('right', array(
		    'header'    => Mage::helper('magicmenu')->__('Block Right'),
		    'align'     =>'left',
		    'index'     => 'right',
		    'type'      => 'options',
		    'options'   => $this->getStaticBlock(),
		));

		$this->addColumn('bottom', array(
		    'header'    => Mage::helper('magicmenu')->__('Block Bottom'),
		    'align'     =>'left',
		    'index'     => 'bottom',
		    'type'      => 'options',
		    'options'   => $this->getStaticBlock(),
		));

		$this->addColumn('left', array(
		    'header'    => Mage::helper('magicmenu')->__('Block Left'),
		    'align'     =>'left',
		    'index'     => 'left',
		    'type'      => 'options',
		    'options'   => $this->getStaticBlock(),
		));

		$this->addColumn('status', array(
				'header'    => Mage::helper('magicmenu')->__('Status'),
				'align'     => 'left',
				'width'     => '80px',
				'index'     => 'status',
				'type'      => 'options',
				'options'   => array(
					1 => 'Enabled',
					2 => 'Disabled',
			),
		));

		$this->addColumn('action',
		  array(
			'header'    =>  Mage::helper('magicmenu')->__('Action'),
			'width'     => '100',
			'type'      => 'action',
			'getter'    => 'getId',
			'actions'   => array(
				array(
					'caption'   => Mage::helper('magicmenu')->__('Edit'),
					'url'       => array('base'=> '*/*/edit'),
					'field'     => 'id'
				)
			),
			'filter'    => false,
			'sortable'  => false,
			'index'     => 'stores',
			'is_system' => true,
		));

		$this->addExportType('*/*/exportCsv', Mage::helper('magicmenu')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('magicmenu')->__('XML'));

		return parent::_prepareColumns();
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('menu_id');
		$this->getMassactionBlock()->setFormFieldName('menu');

		$this->getMassactionBlock()->addItem('delete', array(
			'label'    => Mage::helper('magicmenu')->__('Delete'),
			'url'      => $this->getUrl('*/*/massDelete'),
			'confirm'  => Mage::helper('magicmenu')->__('Are you sure?')
		));

		$statuses = Mage::getSingleton('magicmenu/status')->getOptionArray(); // option Action for change status

		array_unshift($statuses, array('label'=>'', 'value'=>''));
		$this->getMassactionBlock()->addItem('status', array(
			'label'=> Mage::helper('magicmenu')->__('Change status'),
			'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
			'additional' => array(
				'visibility' => array(
					'name' => 'status',
					'type' => 'select',
					'class' => 'required-entry',
					'label' => Mage::helper('magicmenu')->__('Status'),
					'values' => $statuses
				 )
			)
		));
		return $this;
	}

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/edit', array('id' => $row->getId()));
	}

	public function getGridUrl()
	{
		return $this->getUrl('*/*/grid', array('_current'=>true)); // for Ajax in grid
	}

}

