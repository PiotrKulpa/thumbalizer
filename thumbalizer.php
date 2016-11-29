//Adding photos in Codeigniter ^3.0
	public function addphoto()
	{
		//Login to back-end
		$is_logged_in = $this->session->userdata('is_logged_in');
		if(!isset($is_logged_in) || $is_logged_in != true)
		{
			//No authorization
			$this->load->view('templates/header');
			$this->load->view('admin');
			$this->load->view('templates/footer');

		}
		else
		{
			//upload system in CI
			$config['upload_path']          = './uploads/photos';
			$config['allowed_types']        = 'jpg|png';
			$config['max_size']             = 3000;

			$this->load->library('upload', $config);

			//validating upload
			if ( ! $this->upload->do_upload('userfile_photo'))
			{
					//upload error
					$photo_message = array('photo_message' => '<div class="alert alert-danger">Nie wybrałeś pliku, plik jest za duży lub ma złe rozszerzenie</div>');

					$this->load->view('templates/header-panel');
					$this->load->view('panel', $photo_message);
					$this->load->view('templates/footer');

			}
			else
			{
					//upload validation ok
					$data = array('upload_data' => $this->upload->data());
/////////////////////////////////////////////////////////////////////////////////////
					
					//*** Main function based on PHP GD2 library ***
					
					//making thumb in thumb folder
					$myimagename = $this->upload->data('file_name');
					list($width, $height, $type, $attr) = getimagesize("uploads/photos/".$myimagename);
					
					//checking dimension of uploaded image and setting master_dim in config list
					if($width >= $height)
					{
						$mydim = 'height';
					}
					else
					{
						$mydim = 'width';
					}
					
					//making copy of oryginal image smaller with maintain_ratio
					$config['image_library'] = 'GD2';
					$config['source_image'] = 'uploads/photos/'.$myimagename;
					$config['create_thumb'] = FALSE;
					$config['maintain_ratio'] = TRUE;
					$config['master_dim'] = $mydim;
					$config['height'] = '200';
					$config['width'] = '200';
					$config['new_image'] = 'uploads/photos/thumb';

					$this->image_lib->initialize($config);

					if ( ! $this->image_lib->resize())
					{
						echo $this->image_lib->display_errors();
					}else
					{
						//checking dimension of thumb and setting center of thumb
						list($thumbwidth, $thumbheight) = getimagesize("uploads/photos/thumb/".$myimagename);
						if($thumbwidth >= $thumbheight)
						{
							$my_x_axis = ($thumbwidth - 200) / 2 ;
							$my_y_axis = 0;
						}
						else
						{
							$my_y_axis = ($thumbheight - 200) / 2 ;
							$my_x_axis = 0;
						}
					}

					$config2['image_library'] = 'GD2';
					$config2['source_image'] = 'uploads/photos/thumb/'.$myimagename;
					$config2['create_thumb'] = FALSE;
					$config2['maintain_ratio'] = FALSE;
					$config2['height'] = '200';
					$config2['width'] = '200';
					$config2['x_axis'] = $my_x_axis;
					$config2['y_axis'] = $my_y_axis;

					$this->image_lib->initialize($config2);


					if ( ! $this->image_lib->crop())
					{
						echo $this->image_lib->display_errors();
					}else
					{
						//error
					}
/////////////////////////////////////////////////////////////////////////////////////
					//DB query

					$query = $this->Admin_model->add_photo();

					if($query)
					{
						//OK alert
						$photo_message = array('photo_message' => '<div class="alert alert-success">Przesłałeś 1 plik</div>');
						$this->load->view('templates/header-panel');
						$this->load->view('panel', $photo_message);
						$this->load->view('templates/footer');
					}
					else // error alert
					{

						$photo_message = array('photo_message' => '<div class="alert alert-danger">Przesłanie nie powiadło się</div>');
						$this->load->view('templates/header-panel');
						$this->load->view('panel', $photo_message);
						$this->load->view('templates/footer');

					}


			}
		}
	}
