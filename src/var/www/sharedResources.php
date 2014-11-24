<?php


class store extends Stackable
{
    public function run(){

    }
}



class Receiver extends Thread
{
    protected $id;

    protected $stack;

    public function __construct($stack, $id)
    {
        $this->stack = $stack;
        $this->id = $id;
        $this->start();
    }

    public function run()
    {
        while (!feof($this->stack[$this->id])) {  //This looped forever
            $content = fread($this->stack[$this->id], 1024);

            foreach($this->stack["resources"] as $res) {
                if ($res != $this->id) {
                    fwrite($this->stack[$res], $content);
                }
            }
            //fwrite($this->stack[2], $content);
        }
    }
}

$socket = stream_socket_server("tcp://0.0.0.0:8000", $errno, $errstr);

$counter=1;
$receiver = array();
$stack = new store();

while (true) {
    $conn = @stream_socket_accept($socket);
    if(is_resource($conn)) {
        $stack[$counter] = $conn;

        $tmpResRef = $stack["resources"];
        $tmpResRef[] = $counter;
        $stack["resources"] = $tmpResRef;

        $receiver[] = new Receiver($stack, $counter);
        $counter++;
    }
}





?>