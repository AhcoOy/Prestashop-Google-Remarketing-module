<?php

/**
 * 
 * Ahco Oy (http://www.ahco.fi)
 * 
 *  Licensed under The MIT License
 * 
 * @copyright     Copyright (c) Ahco Oy (http://www.ahco.fi)
 * @link          http://www.ahco.fi
 * @since         1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
class ahcogoogleremarketing extends Module {

    /**
     * 
     * 
     */
    protected $mySettings = array(
        'AHCO_G_REMARKETING_CONV_ID' => array(
            'label' => 'Remarketing Conversion ID',
            'default' => '100000000',
        ),
    );

    /**
     *
     * @var type 
     */
    protected $myNecessaryHooks = array(
        'actionProductListOverride',
        'displayFooterProduct',
        'displayOrderConfirmation',
        'displayShoppingCartFooter',
        'displayFooter'
    );


    // https://developers.google.com/adwords-remarketing-tag/parameters?hl=fi

    /**
     * Required. This is the product ID of the product or products displayed on the current page - 
     * the IDs used here should match the IDs in your GMC feed.
     * This parameter should be passed when the ecomm_pagetype is product or cart.
     * On product pages you will generally have a single product 
     * and so a simple single literal value can be passed; 
     * on cart pages if there is more than one product shown (i.e. if the user has more than one product in their cart) 
     * then an array of values can be passed.
     * Both numeric and alphanumeric values are supported,
     * for example 34592212, '23423-131-12', or 'gp232123-19a', 
     * please note that if your product ID is anything other than a number, 
     * then you will need to treat it as a string and surround it in quotes.
     * Example usage on a single product page:
     * var google_tag_params = {
     * ecomm_prodid: 34592212
     * };
     * Example usage on a cart page with more than on product
     * var google_tag_params = {
     * ecomm_prodid: [34592212, '23423-131-12', 'gp232123-19a']
     * };
     * 
     */
    protected $ecomm_prodid = null;

    /**
     * Indicates the type of page that the tag is on. Valid values:
     * home Used on the home page or landing page of your site.
     * searchresults Used on pages where the results of a user's search are displayed.
     * category Used on pages that list multiple items within a category, for example a page showing all shoes in a given style.
     * product Used on individual product pages.
     * cart Used on the cart/basket/checkout page.
     * purchase Used on the page shown once a user has purchased (and so converted), for example a "Thank You" or confirmation page.
     * other Used where the page does not fit into the other types of page, for example a "Contact Us" or "About Us" page.
     * @var type 
     */
    protected $ecomm_pagetype = 'other';

    /**
      This parameter should be used on product, cart and purchase page types and should contain the value of a single product on product pages, or the total sum of the values of one or more products on the cart and purchase pages. The value should be passed as a javascript Number and should not include any currency symbols.
      Example usage on a product page where the product has a value of $9.99:
      var google_tag_params = {
      ecomm_totalvalue: 9.99
      };
      Example usage on the cart or purchase pages where the user's cart contains 3 products with individual values of $29.99, $50.00, and $9.99:
      var google_tag_params = {
      ecomm_totalvalue: 89.98
      };
      Decimal values should be provided where appropriate. When passing integer values the decimal portion can be safely ignored.
      Example usage for a basket where the sum of the products is $110.00:
     * * @var type
     */
    protected $ecomm_totalvalue = null;

    /**
     * This parameter contains a string specifying the category of the currently viewed product or category pages. The string can be any value and does not need to conform to any specific naming convention.
     * Example usage for a product on in the "Home & Garden" category
     *
     *      var google_tag_params = {
     *           ecomm_category: 'Home & Garden'
     *     };
     * @var type 
     */
    protected $ecomm_category = null;

    /**
     * 
     */
    public function __construct() {
        $this->name = 'ahcogoogleremarketing';
        $this->tab = 'advertising_marketing';
        $this->author = 'Ahco Oy / Heikki Pals';
        $this->version = '1.0';

        parent::__construct();
        $this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('Google Remarketing');
        $this->description = $this->l('Module for typical online retailers who are selling products on their web site, e.g. a clothes retailer, an electronics retailer, or a grocery retailer and using a Google Merchant Center (GMC) feed.');
    }

    /**
     * 
     * 
     * @return boolean
     */
    public function install() {

        if (!parent::install()) {
            return false;
        }

        foreach ($this->myNecessaryHooks as $hookName) {
            if (!$this->registerHook($hookName)) {
                $this->uninstall();
                return false;
            }
        }

        foreach ($this->mySettings as $key => $sData) {
            Configuration::updateValue($key, $sData['default']);
        }

        return true;
    }

    /**
     * 
     * @return boolean
     */
    public function uninstall() {
        if (!parent::uninstall()) {
            return false;
        }

        foreach ($this->myNecessaryHooks as $hookName) {
            if (!$this->unregisterHook($hookName)) {
//return false;
            }
        }

        foreach ($this->mySettings as $key => $sData) {
            Configuration::deleteByName($key);
        }

        return true;
    }

    /**
     * Seettings for the module
     * @return type
     */
    public function getContent() {

        foreach ($this->mySettings as $key => $sData) {
            if (isset($_POST[$key])) {

                if (($sData['default'] === null) && ($_POST[$key] == '')) {
                    $_POST[$key] = null;
                }

                Configuration::updateValue($key, $_POST[$key]);
            }
        }

        $html = '<h1>' . $this->l($this->displayName) . '</h1>';
        $html .= '<h2>' . $this->l('Settings') . '</h2>';
        $html .= '<form method="post"><table>';
        foreach ($this->mySettings as $key => $sData) {
            $html .= '<tr>';
            $html .= '<td><pre>' . $this->l($sData['label']) . ' &nbsp;&nbsp;&nbsp;</pre>';
            $html .= '<br/>' . $this->l('Example') . ' &nbsp; `' . htmlspecialchars($sData['default']) . '` &nbsp; </td>';
            $html .= '<td> &nbsp; <input id="' . htmlspecialchars($key) . '" name="' . htmlspecialchars($key) . '"  type="text" value="' . htmlspecialchars(Configuration::get($key)) . '"> </td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
        $html .= '<input type="submit" value="' . htmlspecialchars($this->l('Save')) . '" name="save" " >';
        $html .= '</form>'
        ;

        return $html;
    }

    /**
     * Hook is called in CategoryController
     * @param type $params
     */
    public function hookActionProductListOverride($params) {
        $this->ecomm_pagetype = 'category';

        // Get category ID
        $id_category = (int) Tools::getValue('id_category');
        if (!$id_category || !Validate::isUnsignedId($id_category)) {
            return;
        }
        $category = new Category($id_category, $this->context->language->id);

        $this->ecomm_category = Tools::safeOutput($category->name);
    }

    /**
     * Hook is called in ProductsController
     * @param type $params
     */
    public function hookDisplayFooterProduct($params) {
        $this->ecomm_pagetype = 'product';

        if (!isset($params['product']) || !is_object($params['product'])) {
            return;
        }

        $this->ecomm_prodid = $params['product']->id;

        $this->ecomm_totalvalue = Product::getPriceStatic($params['product']->id);
    }

    /**
     * 
     * @param type $params
     */
    public function hookDisplayShoppingCartFooter($params) {
        if ($this->ecomm_pagetype != 'purchase') {
            $this->ecomm_pagetype = 'cart';
        }

        if (!isset($params['cart']) || !is_object($params['cart'])) {
            return;
        }

        $this->ecomm_totalvalue = $params['cart']->getOrderTotal(true, Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING);

        $this->ecomm_prodid = array();
        $cartProducts = $params['cart']->getProducts();

        foreach ($cartProducts as $product) {
            $this->ecomm_prodid[] = (int) $product['id_product'];
        }
    }

    /**
     * 
     * @param type $params
     */
    public function hookDisplayOrderConfirmation($params) {
        $this->ecomm_pagetype = 'purchase';

        if (!isset($params['objOrder']) || !is_object($params['objOrder'])) {
            return;
        }

        $params['cart'] = new Cart($params['objOrder']->id_cart);
        $this->hookDisplayShoppingCartFooter($params);
    }

    /**
     * 
     * @param type $params
     */
    public function hookDisplayFooter($params) {
        global $smarty;
        $googleConversionId = Configuration::get('AHCO_G_REMARKETING_CONV_ID');

        if (!$googleConversionId || ( $googleConversionId == $this->mySettings['AHCO_G_REMARKETING_CONV_ID']['default'] )) {
            return;
        }

        $googleTagParams = array(
            'ecomm_pagetype' => $this->ecomm_pagetype
        );

        $otherParams = array('ecomm_prodid', 'ecomm_totalvalue', 'ecomm_category');

        foreach ($otherParams as $otherParam) {
            if (isset($this->{$otherParam}) && ( $this->{$otherParam} != null )) {
                $googleTagParams[$otherParam] = $this->{$otherParam};
            }
        }

        $googleTagParamsJson = json_encode($googleTagParams);
        $displayVars = compact('googleConversionId', 'googleTagParamsJson');
        $smarty->assign($displayVars);
        return $this->display(__FILE__, 'views/templates/front/google_remarketing.tpl');
    }

}
