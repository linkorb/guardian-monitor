<?php

namespace Guardian\Monitor\Model;

class CheckRequest
{
    protected $id;
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    protected $check;
    
    public function getCheck()
    {
        return $this->check;
    }
    
    public function setCheck(Check $check)
    {
        $this->check = $check;
        return $this;
    }
    
    protected $requestStamp;
    public function getRequestStamp()
    {
        return $this->requestStamp;
    }
    
    public function setRequestStamp($requestStamp)
    {
        $this->requestStamp = $requestStamp;
        return $this;
    }
    
    protected $responseStamp;
    public function getResponseStamp()
    {
        return $this->responseStamp;
    }
    
    public function setResponseStamp($responseStamp)
    {
        $this->responseStamp = $responseStamp;
        return $this;
    }
    
    protected $agent;
    
    public function getAgent()
    {
        return $this->agent;
    }
    
    public function setAgent($agent)
    {
        $this->agent = $agent;
        return $this;
    }
    
    protected $statusCode;
    public function getStatusCode()
    {
        return $this->statusCode;
    }
    
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }
    
    protected $measurements = [];
    
    public function addMeasurement(Measurement $measurement)
    {
        $this->measurements[] = $measurement;
        return $this;
    }
    
    public function getMeasurements()
    {
        return $this->measurements;
    }
    
    
}
