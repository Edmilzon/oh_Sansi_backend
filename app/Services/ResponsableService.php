<?php

namespace App\Services;

use App\Repositories\ResponsableRepository;

class ResponsableService {

    protected $responsableRepository;

    public function __construct(ResponsableRepository $responsableRepository){
        $this->responsableRepository = $responsableRepository;
    }

    public function getResponsableList(){
        return $this->responsableRepository->getAllResponsables();
    }

    public function createNewResponsable(array $data){
        return $this->responsableRepository->createResponsable($data);
    }
}