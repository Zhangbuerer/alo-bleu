<?php
class Magiccart_Shopbrand_Model_Brand extends Mage_Core_Model_Abstract
{
	public function _construct()
	{
		parent::_construct();
		$this->_init('shopbrand/brand');
	}

    public function getProductCollection()
    {
       	$productIds = explode(",", $this->getProductIds());
        $productCollection = Mage::getModel('catalog/product')
                ->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in' => $productIds));
       return $productCollection;
    }
    
    public function checkIdentifier($identifier, $storeId)
    {
       	$id = 0;
        $stores = array(Mage_Core_Model_App::ADMIN_STORE_ID, $storeId);
       	$select = $this->_getLoadByIdentifierSelect($identifier, $stores, 1);
        if($select) $id = $select->getBrandId();
        return $id;
    }

    protected function _getLoadByIdentifierSelect($identifier, $stores, $isActive = null)
    {
        $brand = Mage::getModel('shopbrand/brand')
                ->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('urlkey', array('eq' => $identifier))
                ->addFieldToFilter('status', array('eq' => $isActive))
                ->getFirstItem(); // khong phai la collection
                //->getSelect()->limit(1); // van la collection ->phai dung foreach
                //echo count($brandCollection); die; //debug

        $storeIds = explode(",", $brand->getStores());
        foreach ($stores as $store) {
            if(in_array($store, $storeIds)) {
                return $brand;
            }
        }
    }
}

