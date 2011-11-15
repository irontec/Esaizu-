<?php
class ExtendedTwitter extends Zend_Service_Twitter
{
    
     /**
     * Retweet a status message
     *
     * @param  int $id ID of status to retweet
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     * @return Zend_Rest_Client_Result
     */
    public function statusRetweet($id)
    {
        $this->_init();
        $path = '/statuses/retweet/' . $this->_validInteger($id) . '.xml';
        $response = $this->_post($path);
        return new Zend_Rest_Client_Result($response->getBody());
    }
    
    
    

}