<?php
use Respect\Validation\Validator as DataValidator;
DataValidator::with('CustomValidations', true);

class CommentController extends Controller {
    const PATH = '/comment';

    private $ticket;
    private $content;

    public function validations() {
        return [
            'permission' => 'user',
            'requestData' => [
                'content' => [
                    'validation' => DataValidator::length(20, 500),
                    'error' => ERRORS::INVALID_CONTENT
                ],
                'ticketNumber' => [
                    'validation' => DataValidator::validTicketNumber(),
                    'error' => ERRORS::INVALID_TICKET
                ]
            ]
        ];
    }

    public function handler() {
        $session = Session::getInstance();
        $this->requestData();

        if ($session->isLoggedWithId($this->ticket->author->id) || Controller::isStaffLogged()) {
            $this->storeComment();
            Response::respondSuccess();
        } else {
            Response::respondError(ERRORS::NO_PERMISSION);
        }
    }

    private function requestData() {
        $ticketNumber = Controller::request('ticketNumber');

        $this->ticket = Ticket::getByTicketNumber($ticketNumber);
        $this->content = Controller::request('content');
    }

    private function storeComment() {
        $comment = new Comment();
        $comment->setProperties(array(
            'content' => $this->content,
            'author' => Controller::getLoggedUser(),
            'date' => Date::getCurrentDate()
        ));

        $this->ticket->ownCommentList->add($comment);
        $this->ticket->store();
    }
}