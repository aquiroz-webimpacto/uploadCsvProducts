<?php
/*
 * 2007-2015 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2015 PrestaShop SA

 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class UploadProducts_csv extends Module
{

    public function __construct()
    {

        $this->name = 'uploadproducts_csv';
        $this->author = 'Aquiroz';
        $this->version = '1.0';
        $this->controllers = array('default');
        $this->bootstrap = true;
        $this->need_instance = 1;

        $this->displayName = $this->l('A Module for upload Products In CSV file');
        $this->description = $this->l('Created for Webimpacto Iniciation');
        $this->confirmUninstall = $this->l('Are you sure, want to remove the module ?');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        parent::__construct();
    }

    public function install()
    {

        if (!parent::install()

        ) {
            return false;
        }

        return true;

    }

    public function uninstall()
    {

        if (!parent::uninstall()

        ) {
            return false;
        }

        return true;

    }

    public function hookActionProductAdd($params)
    {
        $this->_clearCache('*');
    }

    public function getContent()
    {
        return $this->postProcess() . $this->getForm();

    }

    public function getForm()
    {

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->identifier = $this->identifier;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        $helper->languages = $this->context->controller->getLanguages();
        $helper->default_form_language = $this->context->controller->default_form_language;
        $helper->allow_employee_form_lang = $this->context->controller->allow_employee_form_lang;
        $helper->title = $this->displayName;
        $helper->submit_action = 'uploadproducts_csv';

        /**formm array aplicado */

        $this->form[0] = array(
            'form' => array(

                'legend' => array(
                    'title' => $this->displayName,
                ),
                'input' => array(
                    array(
                        'type' => 'file',
                        'label' => $this->l('Upload CSV Products File'),
                        'desc' => $this->l('Upload CSV Products File'),
                        'hint' => $this->l('Upload CSV Products File'),
                        'name' => 'upload_file',
                        'lang' => true,
                    ),
                ),

                'submit' => array(
                    'title' => $this->l('Save'), // This is the button that saves the whole fieldset.
                ),

            ),

        );

        return $helper->generateForm($this->form);

    }

    public function postProcess()
    {

        if (Tools::isSubmit('uploadproducts_csv')) {


            function idProductBrand($BrandName)
            {
                if ($id = \Manufacturer::getIdByName($BrandName)) {
                    return $id;
                } else {
                    $db = \Db::getInstance();
                    $db->insert('manufacturer', array(
                        'name' => $BrandName,
                        'date_upd' => date('Y-m-d H:i:s'),
                    ));
                    idProductBrand($BrandName);
                }
            }


            function idProductTax($tax)
            {
                $db = \Db::getInstance();
                $request = "SELECT id_tax "
                    . "FROM ps_tax "
                    . "WHERE rate = $tax ";
                $id_tax = $db->getValue($request);
                return $id_tax;
            }

           

            function ProductCategoryName($lang, $name)
            {
                $db = \Db::getInstance();
                $request = "SELECT id_category
                        FROM ps_category_lang
                        WHERE  name = '" . $name . "' AND id_lang = " . $lang;
                $id = $db->executeS($request);
                return $id;
            }

            function idProductCategory($name, $lang)
            {
                $result = ProductCategoryName(Configuration::get('PS_LANG_DEFAULT'), $name);
                if ($result) {
                    return $result[0]['id_category'];
                } else {
                    return insertDbCategory(pSQL($name), $lang);
                }
            }

          
            function insertDbCategory($name, $langs)
            {

                $newCategory = new \Category();
                $newCategory->active = 1;
                foreach ($langs as $language) {
                    $names[(int) $language] = $name;
                }
                $newCategory->name = $names;
                $newCategory->id_parent = 2;
                $newCategory->position = 1;
                $newCategory->description = '';
                $newCategory->is_root_category = 1;
                $newCategory->meta_keywords = '';
                $newCategory->meta_description = '';

                $id = createCategoryDb((int) \Context::getContext()->shop->id);

                $newCategory->id_category = $id;
                $newCategory->id = $id;
                $newCategory->update();

                return $id;
            }

         
            function createCategoryDb($shopId)
            {
                $db = \Db::getInstance();

                $db->insert('category', array(
                    'id_parent' => 1,
                    'id_shop_default' => $shopId,
                    'active' => 1,
                    'is_root_category' => 1,
                    'date_add' => date('Y-m-d H:i:s'),
                ));
                $id = Db::getInstance()->Insert_ID();

                return $id;
            }

      
            $csv = $_FILES['upload_file']['tmp_name'];

            $fp = fopen($csv, "r");
            while ($data[] = fgetcsv($fp, 1000, ",")) {
            }
            fclose($fp);
            array_pop($data);

            $numProducts = count($data) - 1;

            for ($i = 1; $i <= $numProducts; $i++) {

                foreach ($langs as $lang) {
                    $names[$lang] = $data[$i][0];
                }
                $product = new Product();
                $product->name = $names;
                $product->reference = $data[$i][1];
                $product->ean13 = $data[$i][2];
                $product->wholesale_price = (float) $data[$i][3];
                $product->price = (float) $data[$i][4];
                $product->redirect_type = '301-category';
                $getProductTaxes = floatval($data[$i][5]);
                $product->id_tax_rules_group = (int) idProductTax($getProductTaxes);
                $product->on_sale = 0;
                $product->id_manufacturer = (int) idProductBrand($data[$i][8]);
                $product->add();
                StockAvailable::setQuantity($product->id, $product->id, (int) $data[$i][6]);

                $categories = explode(';', $data[$i][7]);
                $defaultCategory = 0;
                $idCategories = [];
                foreach ($categories as $category) {
                    $idCategory = idProductCategory(trim($category), $langs);
                    if ($idCategory) {
                        if ($defaultCategory == 0) {
                            $product->id_category_default = $idCategory;
                        }
                        $idCategories[] = $idCategory;
                        $defaultCategory++;
                    }
                }
                if (count($idCategories) > 0) {
                    $product->addToCategories($idCategories);
                }

            }


    
            /**checking csv file for upload csv to upload folder */

            $uploads_dir = $this->local_path.'upload/';
            function uploadfile($origin, $dest, $tmp_name)
                {
                $origin = strtolower(basename($origin));
                $fulldest = $dest.$origin;
                $filename = $origin;
                for ($i=1; file_exists($fulldest); $i++)
                {
                    $fileext = (strpos($origin,'.')===false?'':'.'.substr(strrchr($origin, "."), 1));
                    $filename = substr($origin, 0, strlen($origin)-strlen($fileext)).'['.$i.']'.$fileext;
                    $fulldest = $dest.$newfilename;
                }

                if (move_uploaded_file($tmp_name, $fulldest))
                    return $filename;
                return false;
                }

            uploadfile($_FILES['upload_file']['tmp_name'],$uploads_dir,$_FILES['upload_file']['name']);

            /**end upload products csv */

            return $this->displayConfirmation($this->l('Upload CSV File Successfully'));

        }

    }

}
