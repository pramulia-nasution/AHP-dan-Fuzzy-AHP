<?php

namespace App\Http\AHP;

class Ahp{

    private $countData;
    private $bobotData = [];
    private $matriksPerbandingan = [];
    private $totalBobot = [];
    private $normalisasiMatriks =[];
    private $totalNormalisasi = [];
    private $eigenVektor = [];
    private $totalEigenVektor;
    private $updateMatriksPerbandingan =[];
    private $totalPerbarisMatriks =[];
    private $rerataPerbarisMatriks =[];
    private $lamda;
    private $CI;
    private $IR;
    private $CR;


    function __construct($bobotData)
    {
        $this->bobotData = $bobotData;
        $this->countData = count($this->bobotData);
        $this->_setMatriksPerbandingan();
        $this->_setTotalBobot();
        $this->_setNormalisasiMatriks();
        $this->_setEigenVektor();
        $this->_updateNormalisasiMatriks();
        $this->_setRerataPerbarisMatriks();
    }

    private function _setMatriksPerbandingan(){
        $newData = [];
        $i = 0;
        foreach($this->bobotData as $item){
            $j = 0;
            $newData[$i] = [];
            foreach($item as $ivalue){
                if($i == $j ){
                    $newData[$i][$j++] = '1';
                }   
            $newData[$i][$j++] = $ivalue;
            }
        $i++;
        }
        $newData[$this->countData-1][$this->countData-1] = '1';
        $this->matriksPerbandingan = $newData;
    }

    private function _totalColumnMatriks($params){
        $tempData = [];
        foreach($params as $key => $value){
            $value = array_sum(array_column($params,$key));
            array_push($tempData,$value);
        }
        return $tempData;
    }

    private function _totalRowMatriks($params){
        $tempData = [];
        foreach($params as $value){
            $total = array_sum($value);
            array_push($tempData,$total);
        }
        return $tempData;
    }

    private function getRI($params){
        $RI = [0.00,0.00,0.58,0.90,1.12,1.24,1.32,1.41,1.45,1.49,1.51,1.58];
        return $RI[$params];
    }

    private function _setTotalBobot(){
        $this->totalBobot = $this->_totalColumnMatriks($this->matriksPerbandingan);
    }

    private function _setNormalisasiMatriks(){
        foreach($this->matriksPerbandingan as $value){
           array_walk($value,function (&$item, $index){
                $item /= $this->totalBobot[$index];
            });
            array_push($this->normalisasiMatriks,$value);
        }
        $this->totalNormalisasi = $this->_totalColumnMatriks($this->normalisasiMatriks);
    }

    private function _setEigenVektor(){
        $this->eigenVektor = $this->_totalRowMatriks($this->normalisasiMatriks);
        array_walk($this->eigenVektor,function(&$item){
            $item /= $this->countData;
        });
        $this->totalEigenVektor = array_sum($this->eigenVektor);
    }

    private function _updateNormalisasiMatriks(){
        foreach($this->matriksPerbandingan as $value){
            array_walk($value, function(&$item, $index){
                $item *= $this->eigenVektor[$index];
            });
            array_push($this->updateMatriksPerbandingan,$value);
        }
        $this->totalPerbarisMatriks = $this->_totalRowMatriks($this->updateMatriksPerbandingan);
    }

    private function _setRerataPerbarisMatriks(){
        $this->rerataPerbarisMatriks = $this->totalPerbarisMatriks;
        array_walk($this->rerataPerbarisMatriks,function(&$item,$index){
            $item /= $this->eigenVektor[$index];
        });
        $this->lamda = array_sum($this->rerataPerbarisMatriks) / $this->countData;
        $this->CI = ($this->lamda - $this->countData) / ($this->countData - 1);
        $this->IR = $this->getRI($this->countData-1);
        $this->CR = $this->CI / $this->IR;
    }

    public function getBobotData(){
        return $this->bobotData;
    }

    public function getMatriks(){
        return $this->matriksPerbandingan;
    }
    
    public function getCR(){
        return $this->CR;
    }
}