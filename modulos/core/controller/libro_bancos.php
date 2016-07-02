<?php


class libro_bancos extends fs_controller
{
    protected $db;
    private $uptime;
    private $errors;
    private $messages;
    private $advices;
    private $last_changes;
    private $simbolo_divisas;

    public $user;
    public $page;
    protected $menu;
    public $template;
    public $query;
    public $empresa;
    public $default_items;
    protected $cache;
    public $extensions;
    public function __construct()
    {
        parent::__construct(__CLASS__, 'Libro Bancos', 'libroBanco', FALSE, TRUE);
    }

    protected function public_core()
    {
        $this->template='compras_factura';
    }
 


}