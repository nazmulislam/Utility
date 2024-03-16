<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\Text;

use FuzzyWuzzy\Fuzz;
use FuzzyWuzzy\Process;

class NameMatch {
    
    private function initializeFuzz() : void
    {
        // $process = new Process($fuzz); // CAN MAYBE USE THIS ALSO 
    }
    
    
    public static function ratio($query, $choice)
    {
        $fuzz = new Fuzz();
        return $fuzz->ratio(s1: $query, s2: $choice);
    }
    
    public static function partialRatio($query, $choice)
    {
        $fuzz = new Fuzz();
        return $fuzz->partialRatio(s1: $query, s2: $choice);
    }
    
    public static function tokenSortRatio($query, $choice)
    {
        $fuzz = new Fuzz();
        return $fuzz->tokenSortRatio(s1: $query, s2: $choice);
    }
    
    public static function tokenSetRatio($query, $choice)
    {
        $fuzz = new Fuzz();
        return $fuzz->tokenSetRatio(s1: $query, s2: $choice);
    }

    public static function averageMatchingScore($query, $choice)
    {
        $total = 0;
        $total += NameMatch::ratio($query, $choice);
        $total += NameMatch::partialRatio($query, $choice);
        $total += NameMatch::tokenSetRatio($query, $choice);
        $total += NameMatch::tokenSortRatio($query, $choice);

        return $total / 4;
    }

}
