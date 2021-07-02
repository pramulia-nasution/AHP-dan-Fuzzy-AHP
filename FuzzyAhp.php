<?php 

namespace App\Http\AHP;

class FuzzyAhp{

    private $bobotData = [];
    private $bobotFuzzy = [];
    private $sintesisFuzzy = [];
    private $totalSintesisFuzzy = [];
    private $hasilSI = [];
    private $nilaiVektor = [];
    private $minVektor =[];
    private $totalVektor;
    private $bobotVektor = [];

    function __construct($bobotData)
    {
        $this->bobotData = $bobotData;
        $this->_skalaFuzzy();
    }

    public function calculateFuzzy($data){
        $this->bobotFuzzy = $data;
        $this->_sintesisFuzy();
        $this->_hasilSI();
        $this->_nilaiVektor();
        $this->_bobotVektor();
        $verktor = $this->getBobotVektor();
        return $verktor;
    }

    private function _skalaFuzzy(){
        foreach($this->bobotData as $i => $kriteria){
            foreach($kriteria as $j => $data){
                $this->bobotFuzzy[$i][$j] = $this->getFuzzy($data);
            }
        }
    }

    public function getSkalaFuzzy(){
        return json_encode($this->bobotFuzzy);
    }

    private function _sintesisFuzy(){
        foreach($this->bobotFuzzy as $key => $value){
            $this->sintesisFuzzy[$key] = $this->_countColumns($value);
        }
        foreach($this->sintesisFuzzy as $ikey => $ivalue){
            if($ikey >= 3){
                continue;
            }
            $ivalue = array_sum(array_column($this->sintesisFuzzy,$ikey));
            $this->totalSintesisFuzzy[$ikey] = $ivalue;
        }
        array_walk($this->totalSintesisFuzzy,function(&$item){
            $item = 1/$item;
        });
        $this->totalSintesisFuzzy = array_reverse($this->totalSintesisFuzzy);
    }

    private function _hasilSI(){
        foreach($this->sintesisFuzzy as $value){
            array_walk($value,function (&$item, $index){
                $item *= $this->totalSintesisFuzzy[$index];
            });
            array_push($this->hasilSI,$value);
        }
    }

    private function _nilaiVektor(){
        for($i = 0; $i<count($this->hasilSI);$i++){
            for($j = 0;$j<count($this->hasilSI);$j++){
                if($i == $j){
                    continue;
                }
                if($this->hasilSI[$i][1] >= $this->hasilSI[$j][1]){
                    $this->nilaiVektor[$i][$j] = 1;
                }
                elseif($this->hasilSI[$j][0] >= $this->hasilSI[$i][2]){
                    $this->nilaiVektor[$i][$j] = 0;
                }
                else{
                   $this->nilaiVektor[$i][$j] = $this->hasilSI[$j][0] - $this->hasilSI[$i][2] / ($this->hasilSI[$i][1] - $this->hasilSI[$i][2] ) - ($this->hasilSI[$j][1] - $this->hasilSI[$j][0]);
                }
            }
        }
        foreach($this->nilaiVektor as $key => $value){
            $this->minVektor[$key] = min($value);
        }
        $this->totalVektor = array_sum($this->minVektor);
    }

    private function _bobotVektor(){
        $this->bobotVektor = $this->minVektor;
        array_walk($this->bobotVektor,function(&$item){
            $item /= $this->totalVektor;
        });
    }

    private function _countColumns($params){
        $tempData = [];
        foreach($params as $key => $value){
            if($key >= 3){
                continue;
            }
            $value = array_sum(array_column($params,$key));
            array_push($tempData,$value);
        }
        return $tempData;
    }

    public function getBobotVektor(){
        return $this->bobotVektor;
    }

    private function getFuzzy($value){
        switch($value){
            case '1':
                return [1,1,1];
                break;
            case '2':
            case '4':
            case '6':
            case '8':
                return [($value-1)/2,$value/2,($value+1)/2];
                break;
            case '3':
            case '5':
            case '7':
                return [floor($value/2),$value/2,ceil($value/2)];
                break;
            case '9':
                return [4,4.5,4.5];
                break;
            case '0.5':
                return [2/3,1,2];
                break;
            case '0.25':
                return [0.4,0.5,2/3];
                break;
            case '0.16666666666666666':
                return [2/7,1/3,2/5];
                break;
            case '0.125':
                return [2/9,0.25,2/7];
                break;
            case '0.3333333333333333':
                return [0.5,2/3,1];
                break;
            case '0.2':
                return [1/3,0.4,0.5];
                break;
            case '0.14285714285714285':
                return [0.25,2/7,1/3];
                break;
            case '0.1111111111111111':
                return [2/9,2/9,0.25];
                break;
            default:
                return [0,0,0];
                break;
        }
    }
}
