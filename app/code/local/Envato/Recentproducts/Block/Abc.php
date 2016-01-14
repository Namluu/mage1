<?php
class Envato_Recentproducts_Block_Abc extends Mage_Core_Block_Template
{
	public function getRecentProducts()
	{
		$arr_products = array();
		$products = Mage::getModel('recentproducts/recentproducts')->getRecentProducts();

		foreach ($products as $product) {
			$arr_products[] = array(
				'id' => $product->getId(),
				'name' => $product->getName(),
				'url' => $product->getProductUrl()
				);
		}

		return $arr_products;
	}
}