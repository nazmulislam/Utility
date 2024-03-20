<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Text;

use FuzzyWuzzy\Fuzz;

class TextMatch
{
    public Fuzz $fuzz;

    public function __construct(Fuzz $fuzz)
    {
        $this->setFuzz(fuzz: $fuzz);
    }

    public function setFuzz(Fuzz $fuzz): TextMatch
    {
        $this->fuzz = $fuzz;
        return $this;
    }

    public  function ratio($query, $choice)
    {

        return $this->fuzz->ratio(s1: $query, s2: $choice);
    }

    public  function partialRatio($query, $choice)
    {

        return $this->fuzz->partialRatio(s1: $query, s2: $choice);
    }

    public  function tokenSortRatio($query, $choice)
    {

        return $this->fuzz->tokenSortRatio(s1: $query, s2: $choice);
    }

    public  function tokenSetRatio($query, $choice)
    {

        return $this->fuzz->tokenSetRatio(s1: $query, s2: $choice);
    }

    public  function averageMatchingScore($query, $choice)
    {
        $total = 0;
        $total += $this->ratio($query, $choice);
        $total += $this->partialRatio($query, $choice);
        $total += $this->tokenSetRatio($query, $choice);
        $total += $this->tokenSortRatio($query, $choice);

        return $total / 4;
    }
}
