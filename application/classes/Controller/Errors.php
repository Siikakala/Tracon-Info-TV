<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Errors extends Controller
{
    /**
     * @var string
     */
    protected $_requested_page;

    /**
     * @var string
     */
    protected $_message;

    protected $template = "errors/";

    public function __construct(Request $request, Response $response) {
        parent::__construct($request, $response);

        // Assign the request to the controller
        $this->request = $request;
        // Assign a response to the controller
        $this->response = $response;
    }

    /**
     * Pre determine error display logic
     */
    public function before()
    {
        parent::before();

        // Sub requests only!
        if (Request::$initial !== Request::$current)
        {
            if ($message = rawurldecode($this->request->param('message')))
            {
                $this->_message = $message;
            }

            if ($requested_page = rawurldecode($this->request->param('origuri')))
            {
                $this->_requested_page = $requested_page;
            }
        }
        else
        {
            // This one was directly requested, don't allow
            $this->request->action(404);

            // Set the requested page accordingly
            $this->_requested_page = Arr::get($_SERVER, 'REQUEST_URI');
        }

        $this->response->status((int) $this->request->action());

        //mail("siikakala@tracon.fi","Error ".$this->response->status()." sivulla ".$this->_requested_page,"Katso logi.");
    }

    /**
     * Serves HTTP 404 error page
     */
    public function action_404()
    {

        $this->view = View::factory('errors/404')
            ->set('error_message', $this->_message)
            ->set('requested_page', $this->_requested_page)
            ->set('image', URL::site('/')."imgs/errors/".$this->response->status().".jpg");
    }

    /**
     * Serves HTTP 500 error page
     */
    public function action_500()
    {

        $this->view = View::factory('errors/500')
            ->set('error_message', $this->_message)
            ->set('requested_page', $this->_requested_page)
            ->set('image', URL::site('/')."imgs/errors/".$this->response->status().".jpg");
    }

    public function after(){
        echo $this->view;
    }
}
?>