<?php


class Comment extends Omeka_Record_AbstractRecord
{
    public $id;
    public $record_id;
    public $record_type;
    public $path;
    public $added;
    public $body;
    public $author_email;
    public $author_url;
    public $author_name;
    public $ip;
    public $user_agent;
    public $user_id;
    public $parent_comment_id;
    public $approved;
    public $is_spam;


    public function checkSpam()
    {
        $wordPressAPIKey = get_option('commenting_wpapi_key');
        if(!empty($wordPressAPIKey)) {
            $ak = new Zend_Service_Akismet($wordPressAPIKey, WEB_ROOT );
            $data = $this->getAkismetData();
            try {
                $this->is_spam = $ak->isSpam($data);
            } catch (Exception $e) {
                $this->is_spam = 1;
            }
        } else {
            //if not using Akismet, assume only registered users are commenting
            $this->is_spam = 0;
        }
    }

    public function getAkismetData()
    {
        $data = array(
            'user_ip' => $this->ip,
            'user_agent' => $this->user_agent,
            'permalink' => $this->getAbsoluteUrl(),
            'comment_type' => 'comment',
            'comment_author_email' => $this->author_email,
            'comment_content' => $this->body

        );
        if($this->author_url) {
            $data['comment_author_url'] = $this->author_url;
        }

        if($this->author_name) {
            $data['comment_author_name'] = $this->author_name;
        }
        return $data;
    }
        
    protected function _validate()
    {
        if(trim(strip_tags($this->body)) == '' ) {
            $this->addError('body', "Can't leave an empty comment!");
        }
    }
        
    public function getAbsoluteUrl($includeHash = true) 
    {
        $uri = PUBLIC_BASE_URL . $this->path;
        
        if($includeHash) {
            $uri .= "#comment-" . $this->id;
        }
        return $uri;        
    }
}