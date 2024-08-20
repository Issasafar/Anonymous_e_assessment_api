<?php
class CourseServiceResponse implements JsonSerializable {
    public function jsonSerialize() : array{
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data,
        ];
    }
    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
    private bool $success;

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }
    private $message;
    private $data;

    /**
     * @param $success
     * @param $message
     * @param $data
     */
    public function __construct($success, $message, $data)
    {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
    }

}
