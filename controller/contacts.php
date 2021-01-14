<?php
class ControllerContacts extends Controller {

    private $model;
    private $data = array();

    public function __construct($registry) {
        parent::__construct($registry);
        $this->loads();
    }

	public function index() {

	    $filter_data = array(
	        'name' => false,
            'surname' => false,
            'number' => false
        );

	    if(isset($_GET['name']) && !empty($_GET['name'])){
	        $filter_data['name'] = $_GET['name'];
        }

        if(isset($_GET['surname']) && !empty($_GET['surname'])){
            $filter_data['surname'] = $_GET['surname'];
        }

        if(isset($_GET['number']) && !empty($_GET['number'])){
            $filter_data['number'] = $_GET['number'];
        }

        $search = false;

        if(isset($_GET['search']) && !empty($_GET['search'])){
            $search = $_GET['search'];
        }

	    $this->data['contacts'] = $this->model->getContacts($search,$filter_data);
	    $this->data['title'] = $this->data['text_project'];
	    $this->data['sub_title'] = "Список контактов";

	    $this->data['filter_data'] = $filter_data;
	    $this->data['search'] = $search;

	    echo $this->registry->get('twig')->render('list',$this->data);
    }

    public function view(){
	    if($this->registry->get('id')){
	        $id = $this->registry->get('id');
            $this->data['title'] = $this->data['text_project'];
            $this->data['contact'] = $this->model->getContact($id);
            $this->data['sub_title'] = "Контакт - " . $this->data['contact']['name'];
            $this->data['breadcrumbs'][] = array(
                'title' => $this->data['text_project'],
                'href'  => $this->registry->get('base_page')
            );
            $this->data['breadcrumbs'][] = array(
                'title' => $this->data['contact']['name'],
                'href' =>''
            );
        }else{
            header("Location: ".HTTPS_SERVER);
            die();
        }

        echo $this->registry->get('twig')->render('view',$this->data);
    }

    public function add(){

	    if(isset($_POST) && !empty($_POST)){
	        $data = $_POST;
	        $json = $this->validate($data);
	        if(!isset($json['error'])){
                $this->model->addContact($data);
                $json['location'] = $this->registry->get('base_page');
            }

	        echo json_encode($json);
	        die();
        }
	    $this->data['title'] = $this->data['text_project'];
	    $this->data['sub_title'] = $this->data['text_add_contact'];
	    $this->data['breadcrumbs'][] = array(
	        'title' => $this->data['text_project'],
            'href'  => $this->registry->get('base_page')
        );
	    $this->data['breadcrumbs'][] = array(
	        'title' => $this->data['text_add_contact'],
            'href' =>''
        );

        echo $this->registry->get('twig')->render('form',$this->data);

    }

	public function edit() {

        if(isset($_POST) && !empty($_POST)){
            $data = $_POST;
            $json = $this->validate($data);
            if(!isset($json['error'])){
                $this->model->editContact($data);
                $json['location'] = $this->registry->get('base_page');
            }

            echo json_encode($json);
            die();
        }

        $this->data['title'] = $this->data['text_project'];
        $this->data['sub_title'] = $this->data['text_add_contact'];
        $this->data['breadcrumbs'][] = array(
            'title' => $this->data['text_project'],
            'href'  => $this->registry->get('base_page')
        );

        if($this->registry->get('id')) {
            $this->data['breadcrumbs'][] = array(
                'title' => $this->data['text_edit_contact'],
                'href' =>''
            );

            $this->data['contact'] = $this->model->getContact($this->registry->get('id'));
            $this->data['sub_title'] = $this->data['text_edit_contact'] . ' ' .  $this->data['contact']['name'];

        }else{
            $this->data['breadcrumbs'][] = array(
                'title' => $this->data['text_add_contact'],
                'href' =>''
            );
        }

        echo $this->registry->get('twig')->render('form',$this->data);
    }

    public function remove() {
        if($this->registry->get('id')){
            $id = $this->registry->get('id');
            $this->model->removeContact($id);
        }
        header("Location: ".$this->registry->get('base_page'));

    }

    private function validate($data){
	    $json = array();
        //validations

        if(isset($data['numbers'])){
            $country_code = '+38';
            foreach ($data['numbers'] as $number){
                $number_unic = str_replace($country_code,'',$number); // сводим до строки без кода
                if(strlen($number_unic) != 10){
                    $json['error'] = $this->data['text_error_tel'] . ' ' . $number;
                }
            }
        }

        if(isset($data['birthday']) && !empty($data['birthday'])){
            $date = $data['birthday'];
            if(!$this->validateAge($date)){
                $json['error'] = $this->data['text_birthday_error'];
            }
        }else{
            $json['error'] = $this->data['text_birthday_error'];
        }

        if(isset($data['mail']) && !empty($data['mail']) && !filter_var($data['mail'], FILTER_VALIDATE_EMAIL)){
            $json['error'] = $this->data['text_mail_error'];
        }

        if(isset($data['name']) && strlen($data['name']) < 1){
            $json['error'] = $this->data['text_name_error'];
        }

        $json['numbers'] = $data['numbers'];
        return $json;
    }

    private function validateAge($date)
    {
        // $then will first be a string-date
        $date = strtotime($date);
        //The age to be over, over +18
        $min = strtotime('+18 years', $date);
        if(time() < $min)  {
            return false;
        }else{
            return true;
        }
    }

    private function loads(){
	    require_once DIR_MODEL . 'contacts.php';
	    $this->model = new Contacts($this->registry);
        $_ = array();
        require_once DIR_LANGUAGE . 'all.php';
        $this->data = array_merge($this->data, $_);
        $this->getLinks();

    }

    private function getLinks(){
	    foreach ($this->registry->get('routes')['contacts'] as $key=>$route){
	        $this->data[$key] = HTTPS_SERVER . 'contacts/' . $route;
        }
    }

} 