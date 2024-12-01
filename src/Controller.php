<?php

class Controller{
    public function __construct(private Services $service)
    {

    }

    public function proccessRequest(string $method, ?string $id) : void{

        if($id){
            $this->processIdRequest($method, $id);
        }else{
            $this->processCollectionRequest($method);
        }
    }

    private function processIdRequest(string $method, string $id){
        $product = $this->service->getByID($id);

        if(! $product){
            http_response_code(404);
            echo json_encode(["message" => "Product not found"]);
            return;
        }
        
        switch($method){
            case "GET":
                echo json_encode($product);
                break;
            case "PATCH":
                $data = (array) json_decode(file_get_contents("php://input"), true);

                $errors = $this->getValidationErrors($data, $method);

                if( ! empty($errors)){
                    http_response_code(400);
                    echo json_encode(["errors" => $errors]);
                    break;
                }

                $rowsAffected = $this->service->update($product, $data);

                echo json_encode([
                    "message" => "Product $id updated",
                    "rows" => $rowsAffected
                ]);
                break;
            case "DELETE":
                $rowsAffected = $this->service->delete($id);

                echo json_encode([
                    "message" => "Product $id deleted",
                    "rows" => $rowsAffected
                ]);
                break;

            default:
                http_response_code(405);
                header("Allow: GET, PATCH, DELETE");
        }
        
        
        
    }

    private function processCollectionRequest(string $method){
        switch($method){
            case "GET":
                echo json_encode($this->service->getAll());
                break;
            case "POST":
                $data = (array) json_decode(file_get_contents("php://input"), true);

                $errors = $this->getValidationErrors($data, $method);

                if( ! empty($errors)){
                    http_response_code(400);
                    echo json_encode(["errors" => $errors]);
                    break;
                }

                $id = $this->service->post($data);

                http_response_code(201);
                echo json_encode([
                    "message" => "Product created",
                    "id" => $id
                ]);
                break;
            default:
                http_response_code(405);
                header("Allow: GET, POST");

        }
    }

    private function getValidationErrors(array $data, string $method) : array{
        $errors = [];

        # The name value is NOT optional
        if ($method === "POST" && empty($data["name"])){
            $errors[] = "name is required";
        }

        # The size value is optional
        if (array_key_exists("size", $data)){
            if(filter_var($data["size"], FILTER_VALIDATE_INT) === false){
                $errors[] = "size must be an integer";
            }
        }
        return $errors;
    }
}


?>