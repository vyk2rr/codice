<?php

class Entries_controller extends AppController{
	
	public function __construct(){
		parent::__construct();

		if($this->User->isLogged() === FALSE){
			$this->redirect("login");
		}
	}

	public function index($id = null){
		$P = new post();
		
		$total_rows = $P->countPosts();
		
		//preparing pagination.
		$page = (is_null($page)) ? 1 : $page ;
		$limit = $this->userConf['posts_per_page'];
		$offset = (($page-1) * $limit);
		$limitQuery = $offset.",".$limit;
		
		$targetpage = $this->path.'admin/index/';
		$pagination = $this->pagination->init($total_rows, $page, $limit, $targetpage);
		
		//preparing views
		$this->title_for_layout("Administraci&oacute;n - Codice CMS");
		
		$this->view->pagination = $pagination;
		$this->view->posts = $P->findAll(NULL, "ID DESC", $limitQuery, NULL);
		
		$this->view->blogConfig = $this->blogConfig;
		$this->view->userConf = $this->userConf;

		$this->view->setLayout("admin");
		$this->render();
	}

	public function create(){
		if ($this->data) {
			$P = new post();
			if(isset($this->data['cancelar'])) {
				$this->redirect("admin/");
			}
			
			if (isset($this->data['borrador'])) {
				$this->data['status'] = 'draft';
				unset($this->data['borrador']);
				
			} elseif (isset($this->data['publicar'])) {
				$this->data['status'] = 'publish';
				unset($this->data['publicar']);
			} else {
				$this->redirect("admin/");
			}
			
			if(!preg_match("/\S+/",$this->data['title']) OR $this->data['title'] == ""){
				$this->data['title'] = "Untitled";
			}
			
			$this->data['urlfriendly'] = $P->buildUrl($this->data['title']);
			
			$tags = $this->data['tags'];
			unset($this->data['tags']);
			
			$P->prepareFromArray($this->data);
			$P->save();
			
			$post_id = $P->db->lastId();
			$P->updateTags($post_id,$tags);
			
			$this->redirect("admin/");
		} else {
			$this->view->setLayout("admin");
			$this->title_for_layout($this->l10n->__("Agregar post - Codice CMS"));

			$this->view->blogConfig = $this->blogConfig;
			$this->view->userConf = $this->userConf;

			$this->render();
		}
	}

	public function read(){
		
	}

	public function update(){
		$id = (int) $id;
		if(!$id)$this->redirect('admin');
		
		$statuses = array(
			"publish",
			"draft"
		);
		
		if ($this->data) {
			if(isset($this->data['cancelar'])){
				$this->redirect("admin/");
			}else{
				$P = new post();
				$P->find($id); 
				
				if(!preg_match("/\S+/",$this->data['title']) OR $this->data['title'] == ""){
					$this->data['title'] = "Untitled";
				}
				
				if(!preg_match("/\S+/",$this->data['urlfriendly']) OR $this->data['urlfriendly'] == ""){
					$this->data['urlfriendly'] = $this->data['title'];
				}
				
				$this->data['urlfriendly'] = $P->buildUrl($this->data['urlfriendly'], $id);
				
	 			$P->updateTags($id,$this->data['tags']);
				unset($this->data['tags']);
				
				$P->prepareFromArray($this->data);
				
				$P->save();
				
				$this->session->flash('Información guardada correctamente.');
				
				$this->redirect("admin/edit/$id");
			}
		}
		
		$P = new post();
		
		$post = $P->find($id);
		$post['title'] = utils::convert2HTML($P['title']);
		$post['content'] = utils::convert2HTML($P['content']);
		$post['tags'] = $P->getTags($id,'string');
		
		$this->title_for_layout($this->l10n->__("Editar post - Codice CMS"));
		
		$this->view->id = $id;
		$this->view->post = $post;
		$this->view->statuses = $statuses;
		
		$this->view->blogConfig = $this->blogConfig;
		$this->view->userConf = $this->userConf;

		$this->view->setLayout("admin");
		$this->render();
	}

	public function delete(){
		$P = new post();
		$P->find($id);
		$P->delete();

		$this->redirect("admin/");
	}
	
}