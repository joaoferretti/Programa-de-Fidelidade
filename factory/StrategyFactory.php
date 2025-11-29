<?php
namespace Fidelidade\Factory;

use Fidelidade\Strategies\AccumulationStrategyInterface;
use Fidelidade\Strategies\TierAccumulationStrategy;
use Fidelidade\Strategies\CampaignAccumulationStrategy;

class StrategyFactory
{
    
    public static function createAccumulation(string $type, array $options = []): AccumulationStrategyInterface {
        return match(strtolower($type)) {
            'tier' => new TierAccumulationStrategy($options['tier'] ?? 'bronze'),
            'campaign' => new CampaignAccumulationStrategy($options['multiplier'] ?? 1.0),
            'blackfriday' => new CampaignAccumulationStrategy(5.0), 
            'weekend'     => new CampaignAccumulationStrategy(2.0), 
            default => new TierAccumulationStrategy('bronze'),
        };
    }   
}