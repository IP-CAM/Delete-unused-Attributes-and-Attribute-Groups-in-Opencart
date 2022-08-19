<?php
class ControllerExtensionModuleUnusedAttributes extends Controller {
	private $error = array();

	public function getTotalAttributes() {

        $result = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "attribute");

        return $result->row['total'];
        
	}

	public function getTotalActiveAttributes() {

		$result = $this->db->query("SELECT COUNT(DISTINCT attribute_id) AS total FROM " . DB_PREFIX . "product_attribute");

		return $result->row['total'];

	}

	public function getTotalAttributesGroup() {

        $result = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "attribute_group");

        return $result->row['total'];
        
	}

	public function getTotalActiveAttributesGroup() {

        $results = $this->db->query("SELECT DISTINCT attribute_id FROM " . DB_PREFIX . "product_attribute");

        $attributes = array();

        foreach ($results->rows as $result) {

        	$attributes[]=$result['attribute_id'];

    }

        $result = $this->db->query("SELECT DISTINCT attribute_group_id FROM " . DB_PREFIX . "attribute WHERE attribute_id IN(".implode(",", $attributes).")");

        return $result->num_rows;

	}

	public function deleteUnusedAttributes() {

		$results = $this->db->query("SELECT DISTINCT attribute_id FROM " . DB_PREFIX . "product_attribute");

		$attributes = array();

        foreach ($results->rows as $result) {

        	$attributes[]=$result['attribute_id'];

    }

     	$this->db->query("DELETE FROM " . DB_PREFIX . "attribute WHERE attribute_id NOT IN(".implode(",", $attributes).")");

     
     	$this->db->query("DELETE FROM " . DB_PREFIX . "attribute_description WHERE attribute_id NOT IN(".implode(",", $attributes).")");

    	$this->session->data['success'] = 'Deleted';

		$this->response->redirect($this->url->link('extension/module/UnusedAttributes', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
	}

	public function deleteUnusedAttributesGroup() {

		$results = $this->db->query("SELECT DISTINCT attribute_id FROM " . DB_PREFIX . "product_attribute");

        $attributes = array();

        foreach ($results->rows as $result) {

        	$attributes[]=$result['attribute_id'];

    }

      	$results = $this->db->query("SELECT DISTINCT attribute_group_id FROM " . DB_PREFIX . "attribute WHERE attribute_id IN(".implode(",", $attributes).")");

  		$groups = array();

        foreach ($results->rows as $result) {

        	$groups[]=$result['attribute_group_id'];

    }

      	$this->db->query("DELETE FROM " . DB_PREFIX . "attribute_group WHERE attribute_group_id NOT IN(".implode(",", $groups).")");

      	$this->db->query("DELETE FROM " . DB_PREFIX . "attribute_group_description WHERE attribute_group_id NOT IN(".implode(",", $groups).")");

      	$this->session->data['success'] = 'Deleted';

		$this->response->redirect($this->url->link('extension/module/UnusedAttributes', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));

	}

	public function index() {
		$this->load->language('extension/module/UnusedAttributes');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			if (!isset($this->request->get['module_id'])) {
				$this->model_setting_module->addModule('UnusedAttributes', $this->request->post);
			} else {
				$this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data['attributes_total'] = $this->getTotalAttributes();

        $data['attributes_active_total'] = $this->getTotalActiveAttributes();

        $data['attributes_in_active_total'] = (int)$this->getTotalAttributes()- (int)$this->getTotalActiveAttributes();

        $data['attributes_group_total'] = $this->getTotalAttributesGroup();

        $data['active_attributes_group_total'] = $this->getTotalActiveAttributesGroup();

        $data['in_active_attributes_group_total'] = (int)$this->getTotalAttributesGroup() - (int)$this->getTotalActiveAttributesGroup();

        if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['delete_attribute'] = $this->url->link('extension/module/UnusedAttributes/deleteUnusedAttributes', 'user_token=' . $this->session->data['user_token'], true);

		$data['delete_attribute_group'] = $this->url->link('extension/module/UnusedAttributes/deleteUnusedAttributesGroup', 'user_token=' . $this->session->data['user_token'], true);

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		if (!isset($this->request->get['module_id'])) {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/UnusedAttributes', 'user_token=' . $this->session->data['user_token'], true)
			);
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/UnusedAttributes', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
			);
		}

		if (!isset($this->request->get['module_id'])) {
			$data['action'] = $this->url->link('extension/module/UnusedAttributes', 'user_token=' . $this->session->data['user_token'], true);
		} else {
			$data['action'] = $this->url->link('extension/module/UnusedAttributes', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
		}

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($module_info)) {
			$data['name'] = $module_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($module_info)) {
			$data['status'] = $module_info['status'];
		} else {
			$data['status'] = '';
		}
 
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/UnusedAttributes', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/UnusedAttributes')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		return !$this->error;
	}
}