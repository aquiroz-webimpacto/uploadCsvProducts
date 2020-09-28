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
                        'accept' => $this->l('.csv'),
                        'lang' => false,
                    )
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



        


          
        if (isset($_POST["upload_file"])) {
        
                $csv_file = $_FILES["upload_file"]["tmp_name"];
        
                $data = explode("\n", $csv_file);
                $data = array_filter(array_map("trim", $data));
                $default_language = Configuration::get('PS_LANG_DEFAULT');
            
                $i = 0;
                foreach ($data as $csv) {
                        $i++;
                        if ($i < 2) {continue;}
            
                        $csv_values = explode(",", $csv);
                        $name = $csv_values[0];
                        $reference = $csv_values[1];
                        $ean13 = $csv_values[2];
                        $price = $csv_values[3];
                        $wholesale_price = $csv_values[4];
                        $ecotax = $csv_values[5];
                        $quantity = $csv_values[6];
                        $category = $csv_values[7];
                        $manufacturer = $csv_values[8];
            
                        $product = new Product();
            
                    $product->name = [$default_language => $name];
                    $product->reference = $reference;
                    $product->ean13 =$ean13;
                    $product->price = $price;
                    $product->wholesale_price = $wholesale_price;
                    $product->ecotax = $ecotax;
                    $product->quantity = $quantity;
                    $product->category = $category;
            
                    $product->manufacturer_name = $manufacturer;
            
                    $product->add();
            
            
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
