<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * User controllers class
 *
 * @package     SYSCMS
 * @subpackage  Controllers
 * @category    Controllers
 * @author      Sistiandy Syahbana nugraha <sistiandy.web.id>
 */
class Profile extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if ($this->session->userdata('logged') == NULL) {
            header("Location:" . site_url('admin/auth/login') . "?location=" . urlencode($_SERVER['REQUEST_URI']));
        }
        $this->load->model('User_model');
        $this->load->model('Activity_log_model');
        $this->load->helper(array('form', 'url'));
    }

    // User_customer view in list
    public function index($offset = NULL) {
        $id = $this->session->userdata('user_id');
        if ($this->User_model->get(array('id' => $id)) == NULL) {
            redirect('admin/user');
        }
        $data['user'] = $this->User_model->get(array('id' => $id));
        $data['title'] = 'Detail Profil';
        $data['main'] = 'admin/profile/profile_detail';
        $this->load->view('admin/layout', $data);
    }

    // Add User_customer and Update
    public function edit($id = NULL) {
        $this->load->library('form_validation');

        $this->form_validation->set_rules('user_full_name', 'Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('user_email', 'User Email', 'trim|required|xss_clean|valid_email');
        $this->form_validation->set_error_delimiters('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>', '</div>');
        $data['operation'] = 'Sunting';

        if ($_POST AND $this->form_validation->run() == TRUE) {

            $params['user_id'] = $this->input->post('user_id');
            $params['user_role_role_id'] = $this->input->post('role_id');
            $params['user_last_update'] = date('Y-m-d H:i:s');
            $params['user_full_name'] = $this->input->post('user_full_name');
            $params['user_description'] = $this->input->post('user_description');
            $params['user_email'] = $this->input->post('user_email');
            $status = $this->User_model->add($params);

            // activity log
            $this->Activity_log_model->add(
                    array(
                        'log_date' => date('Y-m-d H:i:s'),
                        'user_id' => $this->session->userdata('user_id'),
                        'log_module' => 'User',
                        'log_action' => 'Sunting',
                        'log_info' => 'ID:'.$status.';Title:' . $this->input->post('user_name')
                    )
            );

            $this->session->set_flashdata('success', $data['operation'] . ' Profil Berhasil');
            redirect('admin/profile');
        } else {

            // Edit mode
            $data['user'] = $this->User_model->get(array('id' => $this->session->userdata('user_id')));
            $data['role'] = $this->User_model->get_role();
            $data['button'] = ($id == $this->session->userdata('user_id')) ? 'Ubah' : 'Reset';
            $data['title'] = $data['operation'] . ' Profil';
            $data['main'] = 'admin/profile/profile_edit';
            $this->load->view('admin/layout', $data);
        }
    }

    function cpw($id = NULL) {
        $this->load->library('form_validation'); 
        $this->form_validation->set_rules('user_password', 'Password', 'trim|required|xss_clean|min_length[6]');
        $this->form_validation->set_rules('passconf', 'Password Confirmation', 'trim|required|xss_clean|min_length[6]|matches[user_password]');
        $this->form_validation->set_error_delimiters('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>', '</div>');
        if ($_POST AND $this->form_validation->run() == TRUE) {
            $id = $this->input->post('user_id');
            $params['user_password'] = sha1($this->input->post('user_password'));
            $status = $this->User_model->change_password($id, $params);

            // activity log
            $this->Activity_log_model->add(
                    array(
                        'log_date' => date('Y-m-d H:i:s'),
                        'user_id' => $this->session->userdata('user_id'),
                        'log_module' => 'User',
                        'log_action' => 'Ubah Password',
                        'log_info' => 'ID:null;Title:' . $this->input->post('user_name')
                    )
            );
            $this->session->set_flashdata('success', 'Ubah Password Berhasil');
            redirect('admin/profile');
        } else {
            if ($this->User_model->get(array('id' => $id)) == NULL) {
                redirect('admin/profile');
            }
            $data['user'] = $this->User_model->get(array('id' => $id));
            $data['title'] = 'Ubah Password';
            $data['main'] = 'admin/profile/change_pass';
            $this->load->view('admin/layout', $data);
        }
    }

}

/* End of file user.php */
/* Location: ./application/controllers/ccp/user.php */
