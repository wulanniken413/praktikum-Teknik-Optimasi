<?php

class Parameters
{
    const FILE_NAME = 'products.txt';
    const COLUMNS = ['item', 'price'];
    const POPULATION_SIZE = 10;
    const BUDGET = 280000;
    const STOPPING_VALUE = 10000;
    const CROSSOVER_RATE = 0.8;
}

class Catalogue
{
    function createProductColumn($listOfRawProduct){
        foreach (array_keys($listOfRawProduct) as $listOfRawProductKey){
            $listOfRawProduct[Parameters::COLUMNS[$listOfRawProductKey]] = $listOfRawProduct[$listOfRawProductKey];
            unset($listOfRawProduct[$listOfRawProductKey]);
        }
        return $listOfRawProduct;

    }
    function product(){
        $collectionOfListProduct = [];
        $raw_data = file(Parameters::FILE_NAME);
        foreach ($raw_data as $listOfRawProduct) {
            $collectionOfListProduct[] = $this->createProductColumn(explode(",", $listOfRawProduct));
        }
        return $collectionOfListProduct;
    }
}

class Individu
{
    function countNumberOfGen()
    {
        $catalogue = new Catalogue;
        return count($catalogue->product());
    }

    function createRandomIndividu()
    {
        for ($i = 0; $i <= $this->countNumberOfGen()-1; $i++){
            $ret[] = rand(0,1);
        }
        return $ret;
    }
}

class Population
{
    function createRandomPopulation(){
        $individu = new Individu;
        for ($i = 0; $i<= Parameters::POPULATION_SIZE-1; $i++){
            $ret[] = $individu->createRandomIndividu();
        }
        return $ret;
    }
}

class Fitness
{
    function selectingItem($individu)
    {
        $catalogue = new Catalogue;
        foreach($individu as $individuKey => $binaryGen){
            if ($binaryGen === 1){
                $ret[] =[
                    'selectedKey' => $individuKey,
                    'selectedPrice' => $catalogue->product()[$individuKey]['price']
                ];
            }
        }
        return $ret;
    }

    function calculateFitnessValue($individu)
    {
        return array_sum(array_column($this->selectingItem($individu),'selectedPrice'));
    }

    function countSelectedItem($individu)
    {
       return count($this->selectingItem($individu));
    }

    function searchBestIndividu($fits, $maxItem, $nuberOfIndividuHasMaxItem)
    {
        if ($nuberOfIndividuHasMaxItem ===1){
            $index = array_search($maxItem, array_column($fits, 'numberOfSelectedItem'));
            return $fits[$index];
        } else {
            foreach ($fits as $key => $val){
                if ($val['numberOfSelectedItem']=== $maxItem){
                    echo $key.' '.$val['fitnessValue'].'<br>';
                    $ret[] =[
                        'individuKey' => $key,
                        'fitnessValue' => $val['fitnessValue']
                    ];
                }
            }
            if (count(array_unique(array_column($ret, 'fitnessValue')))=== 1){
                $index = rand(0, count($ret)- 1);
            } else {
                $max = max(array_column($ret, 'fitnessValue'));
                $index = array_search($max, array_column($ret, 'fitnessValue'));
            }
            echo 'Hasil';
            return $ret[$index];
        }
    }

    function isFound($fits)
    {
       $countedMaxItems = array_count_values(array_column($fits, 'numberOfSelectedItem'));
       print_r($countedMaxItems);
       echo '<br>';
       $maxItem = max(array_keys($countedMaxItems));
       echo $maxItem;
       echo '<br>'; 
       echo $countedMaxItems[$maxItem];
       $nuberOfIndividuHasMaxItem = $countedMaxItems[$maxItem];

       $bestFitnessValue = $this->searchBestIndividu($fits, $maxItem, $nuberOfIndividuHasMaxItem) ['fitnessValue'];
       echo '<br>';
       echo '<br>Best fitness value :'.$bestFitnessValue;

       $residual = Parameters::BUDGET - $bestFitnessValue;
       echo ' Residual:' . $residual;
       if ($residual <= Parameters::STOPPING_VALUE && $residual > 0){
           return TRUE;
       }

    }

    function isFit($fitnessValue)
    {
        if ($fitnessValue <= Parameters::BUDGET){
            return TRUE;
        }
    }

    function fitnessEvaluation ($population)
    {
        $catalogue = new Catalogue;
        foreach ($population as $listOfIndividuKey => $listOfIndividu){
            echo 'Individu-'. $listOfIndividuKey.'<br>';
            foreach ($listOfIndividu as $individuKey => $binaryGen){
                echo $binaryGen.'&nbsp;&nbsp;';
                print_r($catalogue->product()[$individuKey]);
                echo '<br>';
            }
            $fitnessValue = $this->calculateFitnessValue($listOfIndividu);
            $numberOfSelectedItem = $this->countSelectedItem($listOfIndividu);
            echo 'Max. Item: '.$numberOfSelectedItem;
            echo ' fitness Value: '.$fitnessValue;
            if ($this->isFit($fitnessValue)){
                echo ' (Fit)';
                $fits[]= [
                    'selectedIndividuKey' => $listOfIndividuKey,
                    'numberOfSelectedItem' => $numberOfSelectedItem,
                    'fitenessValue' => $fitnessValue
                ];
                print_r($fits);
            } else {
                echo ' (Not Fit)';
            }
            echo '<p>';
        }
        if ($this->isFound($fits)){
            echo ' Found';
        } else{
            echo' >> Next generation';
        }
    }
}

class Crossover
{
    public $population;

    function __construct($population)
    {
        $this->populations = $population;
    }
    function randomZeroToOne()
    {
        return (float) rand() / (float) getrandmax();
    }
    function generateCrossover()
    {
        for ($i = 0; $i <= Parameters::POPULATION_SIZE-1; $i++){
            $randomZeroToOne = $this->randomZeroToOne();
            if ($randomZeroToOne < Parameters::CROSSOVER_RATE){
                $parents[$i] = $randomZeroToOne;
            }
        }
       foreach (array_keys($parents) as $key){
           foreach (array_keys($parents) as $subkey){
               if ($key !== $subkey){
                   $ret[] = [$key, $subkey];
               }
           }
           array_shift($parents);
       }
       return $ret;
    }
    function crossover()
    {
        foreach ($this->generateCrossover() as $listOfCrossover){
            print_r($listOfCrossover);echo '<br>';
        }
    }
}

$initalPopulation = new Population;
$population=$initalPopulation->createRandomPopulation();

$fitness = new Fitness;
$fitness->fitnessEvaluation($population);

$crossover = new Crossover($population);
$crossover->crossover();
//$individu = new Individu;
//print_r($individu->createRandomIndividu());