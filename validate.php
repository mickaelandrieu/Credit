<?php

include __DIR__ . '/vendor/autoload.php';

use Rubix\ML\PersistentModel;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\CrossValidation\MonteCarlo;
use Rubix\ML\CrossValidation\Metrics\Accuracy;
use League\Csv\Reader;

const MODEL_FILE = 'credit.model';

echo '╔═══════════════════════════════════════════════════════════════╗' . "\n";
echo '║                                                               ║' . "\n";
echo '║ Credit Card Default Predictor using Logistic Regression       ║' . "\n";
echo '║                                                               ║' . "\n";
echo '╚═══════════════════════════════════════════════════════════════╝' . "\n";
echo "\n";

$reader = Reader::createFromPath(__DIR__ . '/dataset.csv')
    ->setDelimiter(',')->setEnclosure('"')->setHeaderOffset(0);

$samples = $reader->getRecords([
    'credit_limit', 'gender', 'education', 'marital_status', 'age',
    'timeliness_1', 'timeliness_2', 'timeliness_3', 'timeliness_4',
    'timeliness_5', 'timeliness_6', 'balance_1', 'balance_2', 'balance_3',
    'balance_4', 'balance_5', 'balance_6', 'payment_1', 'payment_2',
    'payment_3', 'payment_4', 'payment_5', 'payment_6', 'avg_balance',
    'avg_payment',
]);

$labels = $reader->fetchColumn('default');

$dataset = Labeled::fromIterator($samples, $labels);

$estimator = PersistentModel::load(new Filesystem(MODEL_FILE));

$simulations = null;

while(!is_numeric($simulations) or $simulations < 2 or $simulations > 50) {
    $simulations = readline('How many simulations to run? (2 - 50): ');
};

echo "\n";

$validator = new MonteCarlo($simulations, 0.2, true);

echo 'Running ' . (string) $simulations . ' monte carlo simulations ...';

$start = microtime(true);

$score = $validator->test($estimator, $dataset, new Accuracy());

echo ' done in ' . (string) (microtime(true) - $start) . ' seconds.' . "\n";

echo "\n";

echo 'Model Accuracy: ' . (string) $score . "\n";
