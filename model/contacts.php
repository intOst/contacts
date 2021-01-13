<?php
class Contacts extends Model {

    public function getContact($id){
        $contact = $this->db->query("SELECT * FROM " . DB_PREFIX . "contact WHERE id = " . (int)$id)->row;
        $contact['birthday'] = date('d-m-Y',strtotime($contact['birthday']));
        $contact['numbers'] = $this->getContactNumbers($id);
        return $contact;
    }

	public function getContacts($search = false,$filter_data = array()) {

        $sql = "SELECT c.* FROM " . DB_PREFIX . "contact c ";

        $where = [];

        if($search && !empty($search)){
            $sql .= " LEFT JOIN " . DB_PREFIX . "contact_number cn ON (cn.contact_id = c.id) ";
            $where[] = " c.name LIKE '%" . $search . "%' " ;
            $where[] = " c.surname LIKE '%" . $search . "%' " ;
            $where[] = " cn.number LIKE '%" . $search . "%' " ;
        }

        if(isset($filter_data['name']) && !empty($filter_data['name'])){
            $where[] = " c.name LIKE '%" . $filter_data['name'] . "%' " ;
        }

        if(isset($filter_data['surname']) && !empty($filter_data['surname'])){
            $where[] = " c.surname LIKE '%" . $filter_data['surname'] . "%' " ;
        }

        if(isset($filter_data['number']) && !empty($filter_data['number'])){
            $sql .= " LEFT JOIN " . DB_PREFIX . "contact_number cn ON (cn.contact_id = c.id) ";
            $where[] = " cn.number LIKE '%" . $filter_data['number'] . "%' " ;
        }

        if(!empty($where)){
            if($search){
                $sql .= " WHERE " . implode(" OR ",$where);
            }else{
                $sql .= " WHERE " . implode(" AND ",$where);
            }
        }

        $sql .= " GROUP BY c.id ";

		$contacts = $this->db->query($sql)->rows;
		foreach ($contacts as $key => $contact){
		    $contacts[$key]['numbers'] = $this->getContactNumbers($contact['id']);
        }
		return $contacts;
	}

	public function getContactNumbers($contact_id){
        $numbers = $this->db->query("SELECT phone_id,number FROM " . DB_PREFIX . "contact_number WHERE contact_id = " . $contact_id)->rows;
        return $numbers;
    }

    public function removeNumber($id){
	    $this->db->query("DELETE FROM " . DB_PREFIX . "contact_number WHERE phone_id = " . $id);
    }

    public function removeContact($id){
        $this->db->query("DELETE FROM " . DB_PREFIX . "contact WHERE id = " . $id);
        $this->db->query("DELETE FROM " . DB_PREFIX . "contact_number WHERE contact_id = " . $id);
    }

    public function addContact($data){
        $date = date('Y-m-d',strtotime($data['birthday']));

        $this->db->query("INSERT INTO " . DB_PREFIX . "contact SET name = '" . $this->db->escape($data['name']) . "', surname = '" . $this->db->escape($data['surname']) . "', mail = '" . $this->db->escape($data['mail']) . "', birthday = '".$date."' ");
        $contact_id = $this->db->getLastId();
        foreach ($data['numbers'] as $number){
            $this->db->query("INSERT INTO " . DB_PREFIX ."contact_number SET contact_id = " . $contact_id . ", number = '" . $this->db->escape($number) ."' ");
        }
    }

    public function editContact($data){
        $date = date('Y-m-d',strtotime($data['birthday']));

        $this->db->query("UPDATE " . DB_PREFIX . "contact SET name = '" . $this->db->escape($data['name']) . "', surname = '" . $this->db->escape($data['surname']) . "', mail = '" . $this->db->escape($data['mail']) . "', birthday = '".$date."' WHERE id = " . $data['id']);

        $this->db->query("DELETE FROM " . DB_PREFIX . "contact_number WHERE contact_id = " . $data['id']);
        foreach ($data['numbers'] as $number){
            $this->db->query("INSERT INTO " . DB_PREFIX ."contact_number SET contact_id = " . $data['id'] . ", number = '" . $this->db->escape($number) ."' ");
        }

    }

}
