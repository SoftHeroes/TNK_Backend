<?php
/* @class InitialOdds
 * @author Piyush
 * @type: Console command
 * @description: Create script to update initial odds for each next games to improve user prediction. */

namespace App\Console\Commands;

use App\Jobs\MailJob;
use App\Models\GameSetup;
use App\Models\InitialOdd;
use App\Models\Rule;
use App\Models\Stock;
use App\Models\StockHistory;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InitialOdds extends Command
{
    protected $signature = 'crawler:initialOdds {--d|debug}';

    protected $description = 'Update initial odds table according the stock history to improve user prediction.';

    protected $minimum = 1.01;

    protected $columns = array();

    public function __construct()
    {
        parent::__construct();
    }

    // Private functions - for internal usage of the class.

    // Get the child rules by parent rule id.
    private function getRules($rulesID)
    {
        $table = new Rule();
        $ids = explode(',', $rulesID);
        $rules = array();
        foreach ($ids as $id) {
            $row = $table->select('name', 'isMatched')->where(['PID' => $id, 'isActive' => 'active'])->first();
            if ($row) {
                $rules[] = $row;
            }
        }

        return $rules;
    }

    // Root value calculation for the sub-3 rules.
    private function rootRadix(
        $digit,
        $defaultPayout
    ) {
        $totalDifference = 100 / 3;

        $digit['total'] = $digit['n1']['total'] + $digit['n2']['total'] + $digit['n3']['total'];

        $digit['n1']['percentage'] = $digit['n1']['total'] / $digit['total'] * 100;
        $digit['n2']['percentage'] = $digit['n2']['total'] / $digit['total'] * 100;
        $digit['n3']['percentage'] = $digit['n3']['total'] / $digit['total'] * 100;

        $digit['n1']['difference'] = $digit['n1']['percentage'] - $totalDifference;
        if ($digit['n1']['difference'] > 0) {
            $temp = $digit['n1']['difference'] / 100 * $defaultPayout;
            $calc = $defaultPayout - $temp;
            $digit['n1']['payout'] = $calc <= $this->minimum ? $this->minimum : $calc;
        } else {
            $temp = abs($digit['n1']['difference'] / 100 * $defaultPayout);
            $digit['n1']['payout'] = $defaultPayout + $temp;
        }

        $digit['n2']['difference'] = $digit['n2']['percentage'] - $totalDifference;
        if ($digit['n2']['difference'] > 0) {
            $temp = $digit['n2']['difference'] / 100 * $defaultPayout;
            $calc = $defaultPayout - $temp;
            $digit['n2']['payout'] = $calc <= $this->minimum ? $this->minimum : $calc;
        } else {
            $temp = abs($digit['n2']['difference'] / 100 * $defaultPayout);
            $digit['n2']['payout'] = $defaultPayout + $temp;
        }

        $digit['n3']['difference'] = $digit['n3']['percentage'] - $totalDifference;
        if ($digit['n3']['difference'] > 0) {
            $temp = $digit['n3']['difference'] / 100 * $defaultPayout;
            $calc = $defaultPayout - $temp;
            $digit['n3']['payout'] = $calc <= $this->minimum ? $this->minimum : $calc;
        } else {
            $temp = abs($digit['n3']['difference'] / 100 * $defaultPayout);
            $digit['n3']['payout'] = $defaultPayout + $temp;
        }

        return $digit;
    }

    // Core calculation for sub-2 rules.
    private function coreEvaluation(
        $digit,
        $defaultPayout
    ) {
        $digit['total'] = $digit['n1']['total'] + $digit['n2']['total'];
        $digit['n1']['percentage'] = $digit['n1']['total'] / $digit['total'] * 100;
        $digit['n2']['percentage'] = $digit['n2']['total'] / $digit['total'] * 100;

        if ($digit['n1']['percentage'] > $digit['n2']['percentage']) {
            $digit['n1']['difference'] = $digit['n1']['percentage'] - $digit['n2']['percentage'];
            $digit['n1']['temp_payout'] = $digit['n1']['difference'] / 100 * $defaultPayout;
            $calc = $defaultPayout - $digit['n1']['temp_payout'];
            $digit['n1']['payout'] = $calc <= $this->minimum ? $this->minimum : $calc;

            $temp = $defaultPayout + $digit['n1']['temp_payout'];
            $digit['n2']['payout'] = $temp <= $this->minimum ? $this->minimum : $temp;
        } else {
            $digit['n2']['difference'] = $digit['n2']['percentage'] - $digit['n1']['percentage'];
            $digit['n2']['temp_payout'] = $digit['n2']['difference'] / 100 * $defaultPayout;
            $calc = $defaultPayout - $digit['n2']['temp_payout'];
            $digit['n2']['payout'] = $calc <= $this->minimum ? $this->minimum : $calc;

            $temp = $defaultPayout + $digit['n2']['temp_payout'];
            $digit['n1']['payout'] = $temp <= $this->minimum ? $this->minimum : $temp;
        }

        return $digit;
    }

    // Prediction of value live changes by the collection of rule array.
    private function prediction(
        $rule,
        $collection,
        $defaultPayout
    ) {
        $digit = array();
        switch ($rule) {
            case "ODD_EVEN":
                $digit['n1']['total'] = array_sum($collection['odd']);
                $digit['n2']['total'] = array_sum($collection['even']);
                $digit = $this->coreEvaluation($digit, $defaultPayout);
                break;
            case "BIG_SMALL":
                $digit['n1']['total'] = array_sum($collection['big']);
                $digit['n2']['total'] = array_sum($collection['small']);
                $digit = $this->coreEvaluation($digit, $defaultPayout);
                break;
            case "HIGH_MIDDLE_LOW":
                $digit['n1']['total'] = array_sum($collection['high']);
                $digit['n2']['total'] = array_sum($collection['middle']);
                $digit['n3']['total'] = array_sum($collection['low']);
                $digit = $this->rootRadix($digit, $defaultPayout);
                break;
            case "FD_NUMBER":
            case "LD_NUMBER":
            case "TD_NUMBER":
            case "BD_NUMBER":
                if ($rule == 'FD_NUMBER') {
                    $type = 'FD_';
                } elseif ($rule == 'LD_NUMBER') {
                    $type = 'LD_';
                } elseif ($rule == 'BD_NUMBER') {
                    $type = 'BD_';
                } elseif ($rule == 'TD_NUMBER') {
                    $type = 'TD_';
                }
                $digit['min'] = min($collection);
                foreach ($collection as $number => $value) {
                    $digit['number'][$number] = $value - $digit['min'];
                }
                $digit['total'] = array_sum($digit['number']);
                foreach ($digit['number'] as $number => $value) {
                    $digit['number'][$number] = [
                        'key' => $type . $number,
                        'repeat' => $value,
                        'leftOverPayout' => ($value / $digit['total'] * 100) / 100 * $defaultPayout,
                        'newPayout' => $defaultPayout - (($value / $digit['total'] * 100) / 100 * $defaultPayout),
                    ];
                }
                foreach ($digit['number'] as $number => $value) {
                    foreach ($digit['number'] as $number2 => $value2) {
                        if ($value2['repeat'] != $value['repeat'] && $value['repeat'] > $value2['repeat']) {
                            $digit['number'][$number2]['newPayout'] += $value['leftOverPayout'] / 9;
                        }
                    }
                }
                break;
            case "BOTH_BIG_SMALL_TIE":
            case "TWO_BIG_SMALL_TIE":
                if ($rule == 'BOTH_BIG_SMALL_TIE') {
                    $digit['n1']['total'] = array_sum($collection['BD_BIG']);
                    $digit['n1']['key'] = 'BD_BIG';
                    $digit['n2']['total'] = array_sum($collection['BD_SMALL']);
                    $digit['n2']['key'] = 'BD_SMALL';
                    $digit['n3']['total'] = array_sum($collection['BD_TIE']);
                    $digit['n3']['key'] = 'BD_TIE';
                } elseif ($rule == 'TWO_BIG_SMALL_TIE') {
                    $digit['n1']['total'] = array_sum($collection['TD_BIG']);
                    $digit['n1']['key'] = 'TD_BIG';
                    $digit['n2']['total'] = array_sum($collection['TD_SMALL']);
                    $digit['n2']['key'] = 'TD_SMALL';
                    $digit['n3']['total'] = array_sum($collection['TD_TIE']);
                    $digit['n3']['key'] = 'TD_TIE';
                }
                $digit = $this->rootRadix($digit, $defaultPayout);
                break;
            default:
                echo "No group, Is `break;` statement misplaced?";
        }

        return $digit;
    }

    // Fragment the matches rule - divide and play.
    private function fragmentChunk(
        $cipher,
        $rule
    ) {
        $array = array();
        $isMatched = explode(',', $rule);
        foreach ($cipher as $digit => $totalRepeat) {
            if (in_array($digit, $isMatched)) {
                $array[(int) $digit] = $totalRepeat;
            }
        }

        return $array;
    }

    // Group by condition as a backup for code crashing during multi calculation.
    private function reckoningCipher(
        $group,
        $cipher,
        $match
    ) {
        $array = array();
        switch ($group) {
            case "FIRST_DIGIT":
                $array = $this->fragmentChunk($cipher, $match);
                break;
            case "LAST_DIGIT":
                $array = $this->fragmentChunk($cipher, $match);
                break;
            case "BOTH_DIGIT":
                $array = $this->fragmentChunk($cipher, $match);
                break;
            case "TWO_DIGIT":
                $array = $this->fragmentChunk($cipher, $match);
                break;
            default:
                echo "Cipher reckoning failed during root initialize of bulk values.";
        }

        return $array;
    }

    // Get last two digits from the each stock value.
    private function getLastTwoDigit(
        $number,
        $precision
    ) {
        $temp = substr(explode('.', $number)[1], 0, $precision);
        $lastTwoDigit = substr($temp, -2);

        return $lastTwoDigit;
    }

    // Find the first digit in stock value.
    private function firstDigit(
        $stockHistory,
        $precision
    ) {
        $decimal = array();
        foreach ($stockHistory as $singleStock) {
            $temp = $this->getLastTwoDigit($singleStock->stockValue, $precision);
            $firstDigit = substr($temp, 0, 1);
            $decimal[] = $firstDigit;
        }

        return $decimal;
    }

    // Find the first digit in stock value.
    private function lastDigit(
        $stockHistory,
        $precision
    ) {
        $decimal = array();
        foreach ($stockHistory as $singleStock) {
            $temp = $this->getLastTwoDigit($singleStock->stockValue, $precision);
            $firstDigit = substr($temp, 1, 2);
            $decimal[] = $firstDigit;
        }

        return $decimal;
    }

    // Find the both digit in stock value.
    private function bothDigit(
        $stockHistory,
        $precision
    ) {
        $decimal = array();
        foreach ($stockHistory as $singleStock) {
            $temp = $this->getLastTwoDigit($singleStock->stockValue, $precision);
            $decimal[] = array_sum(str_split($temp));
        }

        return $decimal;
    }

    // Find the two digit in stock value.
    private function twoDigit(
        $stockHistory,
        $precision
    ) {
        $decimal = array();
        foreach ($stockHistory as $singleStock) {
            $decimal[] = $this->getLastTwoDigit($singleStock->stockValue, $precision);
        }

        return $decimal;
    }

    // Format the new payout for initial odds.
    private function organize($value)
    {
        return number_format(round($value, 2), 2);
    }

    // Insert the calculated result into the database.
    private function injectToDatabase()
    {
        DB::transaction(function () {
            foreach ($this->columns as $stockId => $dataSet) {
                InitialOdd::updateOrCreate(['stockID' => $stockId], $dataSet);
            }
        });
    }

    // Computing the script during the each first startup.
    private function compute($debug)
    {
        $this->info('Fetching stock URLs origin.');

        // 1. Get all registered active stock.
        $stock = new Stock();
        $stockData = $stock->select('PID', 'precision')->where('isActive', 'active')->get();
        $progress = $this->output->createProgressBar(count($stockData));
        $progress->setFormat('debug');

        // 2. Get all today stock history by `stockID`.
        $stockHistory = new StockHistory();
        $stockHistoryData = $precision = array();
        foreach ($stockData as $stockDataValue) {
            $temp = $stockHistory->select('stockID', 'stockValue')->where(['createdDate' => today(), 'stockID' => $stockDataValue['PID']])->orderByDesc('PID')->get();
            if (count($temp) > 0) {
                $stockHistoryData[$stockDataValue['PID']] = $temp;
                $precision[$stockDataValue['PID']] = $stockDataValue->precision;
            }
        }

        // 3. Get default game setup.
        $gameSetup = new GameSetup();
        $gameSetupData = $gameSetup->select(DB::raw('gameName, rulesID, (initialOdd-(commission/100)) AS payout'))->where(['isActive' => 'active'])->groupBy('gameName')->orderBy('PID')->get();

        // 4. Extract decimal values from the calculated result.
        // 4.1 - Stock history loop - To perform mathematical operation on each stock.
        $this->info("Calculating new initial payout.");
        $progress->start();
        foreach ($stockHistoryData as $stockHistoryDataKey => $stockHistoryDataValue) {
            // Debug mode on.
            if ($debug) {
                $this->info(' STOCK:' . $stockHistoryDataKey . '(' . count($stockHistoryDataValue) . ')');
            }

            $DIGIT = array();

            // 4.2 - Game setup loop to find and operate each group rule.
            foreach ($gameSetupData as $gameSetupDataValue) {
                // Debug mode on.
                if ($debug) {
                    $this->line($gameSetupDataValue->gameName);
                }
                // Rules - All Game's rule will be executed one by one.
                // Rule 1 | First and Last digit, Calculation of Big and Small.
                if ($gameSetupDataValue->gameName === 'FD_BigSmall' || $gameSetupDataValue->gameName === 'LD_BigSmall') {
                    // Rules | Find child rules by group rule.
                    $rules = $this->getRules($gameSetupDataValue->rulesID);

                    // Calculation of prediction for big and small digit and update it's to columns.
                    if ($gameSetupDataValue->gameName === 'FD_BigSmall') {
                        // First digit Big & Small | Game setup loop condition for each rules with loop of each stock.
                        $decimal = $this->firstDigit($stockHistoryDataValue, $precision[$stockHistoryDataKey]);
                        $decimal = array_count_values($decimal);

                        foreach ($rules as $rule) {
                            // Debug mode on.
                            if ($debug) {
                                $this->info("\t" . $rule->name . ":(" . $rule['isMatched'] . '-' . $gameSetupDataValue->payout . ')');
                            }
                            // Fragment the each stock value numbers by each rule.
                            if ($rule['name'] === 'FD_BIG') {
                                $tempRuleName = 'big';
                            } elseif ($rule['name'] === 'FD_SMALL') {
                                $tempRuleName = 'small';
                            }
                            $DIGIT['first'][$tempRuleName] = $this->reckoningCipher('FIRST_DIGIT', $decimal, $rule['isMatched']);
                        }

                        // Calculation of prediction for First digit Big and Small.
                        $prediction = $this->prediction('BIG_SMALL', $DIGIT['first'], $gameSetupDataValue->payout);
                        $this->columns[$stockHistoryDataKey]['FD_BIG'] = $this->organize($prediction['n1']['payout']);
                        $this->columns[$stockHistoryDataKey]['FD_SMALL'] = $this->organize($prediction['n2']['payout']);
                    } elseif ($gameSetupDataValue->gameName === 'LD_BigSmall') {
                        // Last digit Big & Small | Game setup loop condition for each rules with loop of each stock.
                        $decimal = $this->lastDigit($stockHistoryDataValue, $precision[$stockHistoryDataKey]);
                        $decimal = array_count_values($decimal);

                        foreach ($rules as $rule) {
                            // Debug mode on.
                            if ($debug) {
                                $this->info("\t" . $rule->name . ":(" . $rule['isMatched'] . '-' . $gameSetupDataValue->payout . ')');
                            }
                            // Fragment the stock value numbers by each rule.
                            if ($rule['name'] === 'LD_BIG') {
                                $tempRuleName = 'big';
                            } elseif ($rule['name'] === 'LD_SMALL') {
                                $tempRuleName = 'small';
                            }
                            $DIGIT['last'][$tempRuleName] = $this->reckoningCipher('LAST_DIGIT', $decimal, $rule['isMatched']);
                        }

                        // Calculation of prediction for Last digit Big and Small.
                        $prediction = $this->prediction('BIG_SMALL', $DIGIT['last'], $gameSetupDataValue->payout);
                        $this->columns[$stockHistoryDataKey]['LD_BIG'] = $this->organize($prediction['n1']['payout']);
                        $this->columns[$stockHistoryDataKey]['LD_SMALL'] = $this->organize($prediction['n2']['payout']);
                    }
                }

                // Rule 2 | First and Last digit, Odd and Even.
                if ($gameSetupDataValue->gameName === 'FD_OddEven' || $gameSetupDataValue->gameName === 'LD_OddEven' || $gameSetupDataValue->gameName === 'TD_OddEven') {
                    if ($gameSetupDataValue->gameName === 'FD_OddEven') {
                        // First digit Odd & Even | Game setup loop condition for each rules with loop of each stock.
                        $decimal = $this->firstDigit($stockHistoryDataValue, $precision[$stockHistoryDataKey]);
                        $decimal = array_count_values($decimal);

                        // Rules | Find child rules.
                        $rules = $this->getRules($gameSetupDataValue->rulesID);

                        // Apply child rule.
                        foreach ($rules as $rule) {
                            // Debug mode on.
                            if ($debug) {
                                $this->info("\t" . $rule->name . ":(" . $rule['isMatched'] . '-' . $gameSetupDataValue->payout . ')');
                            }
                            if ($rule['name'] === 'FD_ODD') {
                                $tempRuleName = 'odd';
                            } elseif ($rule['name'] === 'FD_EVEN') {
                                $tempRuleName = 'even';
                            }
                            $DIGIT['first'][$tempRuleName] = $this->reckoningCipher('FIRST_DIGIT', $decimal, $rule['isMatched']);
                        }

                        // Calculation of prediction for first digit odd and even.
                        $prediction = $this->prediction('ODD_EVEN', $DIGIT['first'], $gameSetupDataValue->payout);
                        $this->columns[$stockHistoryDataKey]['FD_ODD'] = $this->organize($prediction['n1']['payout']);
                        $this->columns[$stockHistoryDataKey]['FD_EVEN'] = $this->organize($prediction['n2']['payout']);
                    } elseif ($gameSetupDataValue->gameName === 'LD_OddEven') {
                        // Last digit Odd & Even | Game setup loop condition for each rules with loop of each stock.
                        $decimal = $this->lastDigit($stockHistoryDataValue, $precision[$stockHistoryDataKey]);
                        $decimal = array_count_values($decimal);

                        // Rules | Find child rules.
                        $rules = $this->getRules($gameSetupDataValue->rulesID);

                        // Apply child rule.
                        foreach ($rules as $rule) {
                            // Debug mode on.
                            if ($debug) {
                                $this->info("\t" . $rule->name . ":(" . $rule['isMatched'] . '-' . $gameSetupDataValue->payout . ')');
                            }
                            if ($rule['name'] === 'LD_ODD') {
                                $tempRuleName = 'odd';
                            } elseif ($rule['name'] === 'LD_EVEN') {
                                $tempRuleName = 'even';
                            }
                            $DIGIT['last'][$tempRuleName] = $this->reckoningCipher('LAST_DIGIT', $decimal, $rule['isMatched']);
                        }

                        // Calculation of prediction for Last digit Odd and Even.
                        $prediction = $this->prediction('ODD_EVEN', $DIGIT['last'], $gameSetupDataValue->payout);
                        $this->columns[$stockHistoryDataKey]['LD_ODD'] = $this->organize($prediction['n1']['payout']);
                        $this->columns[$stockHistoryDataKey]['LD_EVEN'] = $this->organize($prediction['n2']['payout']);
                    } elseif ($gameSetupDataValue->gameName === 'TD_OddEven') {
                        // Tow digit Odd & Even | Game setup loop condition for each rule with loop of each stock.
                        $decimal = $this->twoDigit($stockHistoryDataValue, $precision[$stockHistoryDataKey]);
                        $decimal = array_count_values($decimal);

                        // Rules | Find child rules.
                        $rules = $this->getRules($gameSetupDataValue->rulesID);

                        // Apply child rule.
                        foreach ($rules as $rule) {
                            // Debug mode on.
                            if ($debug) {
                                $this->info("\t" . $rule->name . ":(" . $rule['isMatched'] . '-' . $gameSetupDataValue->payout . ')');
                            }
                            if ($rule['name'] === 'TD_ODD') {
                                $tempRuleName = 'odd';
                            } elseif ($rule['name'] === 'TD_EVEN') {
                                $tempRuleName = 'even';
                            }
                            $DIGIT['two'][$tempRuleName] = $this->reckoningCipher('TWO_DIGIT', $decimal, $rule['isMatched']);
                        }

                        // Calculation of prediction for Two digit Odd and Even.
                        $prediction = $this->prediction('ODD_EVEN', $DIGIT['two'], $gameSetupDataValue->payout);
                        $this->columns[$stockHistoryDataKey]['TD_ODD'] = $this->organize($prediction['n1']['payout']);
                        $this->columns[$stockHistoryDataKey]['TD_EVEN'] = $this->organize($prediction['n2']['payout']);
                    }
                }

                // Rule 3 | First and Last digit - Up, Middle and Low.
                if ($gameSetupDataValue->gameName === 'FD_HighMiddleLow' || $gameSetupDataValue->gameName === 'LD_HighMiddleLow' || $gameSetupDataValue->gameName === 'TD_HighMiddleLow') {
                    if ($gameSetupDataValue->gameName === 'FD_HighMiddleLow') {
                        // First digit High, Middle and Low | Game setup loop condition for each rule with loop of each stock.
                        $decimal = $this->firstDigit($stockHistoryDataValue, $precision[$stockHistoryDataKey]);
                        $decimal = array_count_values($decimal);

                        // Rules | Find child rules.
                        $rules = $this->getRules($gameSetupDataValue->rulesID);

                        // Apply child rule.
                        foreach ($rules as $rule) {
                            // Debug mode on.
                            if ($debug) {
                                $this->info("\t" . $rule->name . ":(" . $rule['isMatched'] . '-' . $gameSetupDataValue->payout . ')');
                            }
                            if ($rule['name'] === 'FD_HIGH') {
                                $tempRuleName = 'high';
                            } elseif ($rule['name'] === 'FD_MIDDLE') {
                                $tempRuleName = 'middle';
                            } elseif ($rule['name'] === 'FD_LOW') {
                                $tempRuleName = 'low';
                            }
                            $DIGIT['first'][$tempRuleName] = $this->reckoningCipher('FIRST_DIGIT', $decimal, $rule['isMatched']);
                        }

                        // Calculation of prediction for Up, Middle and Low digit.
                        $prediction = $this->prediction('HIGH_MIDDLE_LOW', $DIGIT['first'], $gameSetupDataValue->payout);
                        $this->columns[$stockHistoryDataKey]['FD_HIGH'] = $this->organize($prediction['n1']['payout']);
                        $this->columns[$stockHistoryDataKey]['FD_MIDDLE'] = $this->organize($prediction['n2']['payout']);
                        $this->columns[$stockHistoryDataKey]['FD_LOW'] = $this->organize($prediction['n3']['payout']);
                    } elseif ($gameSetupDataValue->gameName === 'LD_HighMiddleLow') {
                        // Last digit High, Middle and Low | Game setup loop condition for each rule with loop of each stock.
                        $decimal = $this->lastDigit($stockHistoryDataValue, $precision[$stockHistoryDataKey]);
                        $decimal = array_count_values($decimal);

                        // Rules | Find child rules.
                        $rules = $this->getRules($gameSetupDataValue->rulesID);

                        // Apply child rule.
                        foreach ($rules as $rule) {
                            // Debug mode on.
                            if ($debug) {
                                $this->info("\t" . $rule->name . ":(" . $rule['isMatched'] . '-' . $gameSetupDataValue->payout . ')');
                            }
                            if ($rule['name'] === 'LD_HIGH') {
                                $tempRuleName = 'high';
                            } elseif ($rule['name'] === 'LD_MIDDLE') {
                                $tempRuleName = 'middle';
                            } elseif ($rule['name'] === 'LD_LOW') {
                                $tempRuleName = 'low';
                            }
                            $DIGIT['last'][$tempRuleName] = $this->reckoningCipher('LAST_DIGIT', $decimal, $rule['isMatched']);
                        }

                        // Calculation of prediction for High, Middle and Low digit.
                        $prediction = $this->prediction('HIGH_MIDDLE_LOW', $DIGIT['last'], $gameSetupDataValue->payout);
                        $this->columns[$stockHistoryDataKey]['LD_HIGH'] = $this->organize($prediction['n1']['payout']);
                        $this->columns[$stockHistoryDataKey]['LD_MIDDLE'] = $this->organize($prediction['n2']['payout']);
                        $this->columns[$stockHistoryDataKey]['LD_LOW'] = $this->organize($prediction['n3']['payout']);
                    } elseif ($gameSetupDataValue->gameName === 'TD_HighMiddleLow') {
                        // Third digit High, Middle and Low | Game setup loop condition for each rule with loop of each stock.
                        $decimal = $this->twoDigit($stockHistoryDataValue, $precision[$stockHistoryDataKey]);
                        $decimal = array_count_values($decimal);

                        // Rules | Find child rules.
                        $rules = $this->getRules($gameSetupDataValue->rulesID);

                        // Apply child rule.
                        foreach ($rules as $rule) {
                            // Debug mode on.
                            if ($debug) {
                                $this->info("\t" . $rule->name . ":(" . $rule['isMatched'] . '-' . $gameSetupDataValue->payout . ')');
                            }
                            if ($rule == 'TD_HIGH') {
                                $tempRuleName = 'high';
                            } elseif ($rule == 'TD_MIDDLE') {
                                $tempRuleName = 'middle';
                            } elseif ($rule == 'TD_LOW') {
                                $tempRuleName = 'TD_LOW';
                            }
                            $DIGIT['two'][$tempRuleName] = $this->reckoningCipher('TWO_DIGIT', $decimal, $rule['isMatched']);

                            // Calculation of prediction for High, Middle and Low digit.
                            $prediction = $this->prediction('HIGH_MIDDLE_LOW', $DIGIT['last'], $gameSetupDataValue->payout);
                            $this->columns[$stockHistoryDataKey]['TD_HIGH'] = $this->organize($prediction['n1']['payout']);
                            $this->columns[$stockHistoryDataKey]['TD_MIDDLE'] = $this->organize($prediction['n2']['payout']);
                            $this->columns[$stockHistoryDataKey]['TD_LOW'] = $this->organize($prediction['n3']['payout']);
                        }
                    }
                }

                // Rule 4 | First, Last and Both digit Number.
                if ($gameSetupDataValue->gameName === 'FD_Number' || $gameSetupDataValue->gameName === 'LD_Number' || $gameSetupDataValue->gameName === 'BD_Number' || $gameSetupDataValue->gameName === 'TD_Number') {
                    // First and Last digit number | Game setup loop condition for each rule with loop of each stock.
                    if ($gameSetupDataValue->gameName === 'FD_Number') {
                        $groupRule = 'FIRST_DIGIT';
                        $ruleType = 'FD_NUMBER';
                        $type = 'first';
                        $decimal = $this->firstDigit($stockHistoryDataValue, $precision[$stockHistoryDataKey]);
                    } elseif ($gameSetupDataValue->gameName === 'LD_Number') {
                        $groupRule = 'LAST_DIGIT';
                        $ruleType = 'LD_NUMBER';
                        $type = 'last';
                        $decimal = $this->lastDigit($stockHistoryDataValue, $precision[$stockHistoryDataKey]);
                    } elseif ($gameSetupDataValue->gameName === 'BD_Number') {
                        $groupRule = 'BOTH_DIGIT';
                        $ruleType = 'BD_NUMBER';
                        $type = 'both';
                        $decimal = $this->bothDigit($stockHistoryDataValue, $precision[$stockHistoryDataKey]);
                    } elseif ($gameSetupDataValue->gameName === 'TD_Number') {
                        $groupRule = 'TWO_DIGIT';
                        $ruleType = 'TD_NUMBER';
                        $type = 'two';
                        $decimal = $this->twoDigit($stockHistoryDataValue, $precision[$stockHistoryDataKey]);
                    }

                    // Rules | Find child rules.
                    $decimal = array_count_values($decimal);
                    $rules = $this->getRules($gameSetupDataValue->rulesID);

                    // Apply child rule.
                    $temp = array();
                    foreach ($rules as $rule) {
                        // Debug mode on.
                        if ($debug) {
                            $this->info("\t" . $rule->name . ":(" . $rule['isMatched'] . '-' . $gameSetupDataValue->payout . ')');
                        }
                        $mergeLastNumber = array_merge($temp, $this->reckoningCipher($groupRule, $decimal, $rule['isMatched']));
                        $temp = $mergeLastNumber;
                    }
                    $DIGIT[$type]['number'] = $temp;

                    // Calculation of prediction for fixed number.
                    $prediction = $this->prediction($ruleType, $DIGIT[$type]['number'], $gameSetupDataValue->payout);
                    foreach ($prediction['number'] as $payout) {
                        $this->columns[$stockHistoryDataKey][$payout['key']] = $this->organize($payout['newPayout']);
                    }
                }

                // Rule 5 | Both digit - Big, Small, Tie.
                if ($gameSetupDataValue->gameName === 'BD_BigSmallTie' || $gameSetupDataValue->gameName === 'TD_BigSmallTie') {
                    if ($gameSetupDataValue->gameName === 'BD_BigSmallTie') {
                        $decimal = $this->bothDigit($stockHistoryDataValue, $precision[$stockHistoryDataKey]);
                        $groupRule = 'BOTH_DIGIT';
                        $type = 'both';
                        $ruleType = 'BOTH_BIG_SMALL_TIE';
                    } elseif ($gameSetupDataValue->gameName === 'TD_BigSmallTie') {
                        $decimal = $this->twoDigit($stockHistoryDataValue, $precision[$stockHistoryDataKey]);
                        $groupRule = 'TWO_DIGIT';
                        $type = 'two';
                        $ruleType = 'TWO_BIG_SMALL_TIE';
                    }

                    // Rules | Find child rules.
                    $decimal = array_count_values($decimal);
                    $rules = $this->getRules($gameSetupDataValue->rulesID);
                    foreach ($rules as $rule) {
                        // Debug mode on.
                        if ($debug) {
                            $this->info("\t" . $rule->name . ":(" . $rule['isMatched'] . '-' . $gameSetupDataValue->payout . ')');
                        }
                        $DIGIT[$type][$rule->name] = $this->reckoningCipher($groupRule, $decimal, $rule['isMatched']);
                    }

                    // Calculating prediction for each number.
                    $prediction = $this->prediction($ruleType, $DIGIT[$type], $gameSetupDataValue->payout);
                    $this->columns[$stockHistoryDataKey][$prediction['n1']['key']] = $this->organize($prediction['n1']['payout']);
                    $this->columns[$stockHistoryDataKey][$prediction['n2']['key']] = $this->organize($prediction['n2']['payout']);
                    $this->columns[$stockHistoryDataKey][$prediction['n3']['key']] = $this->organize($prediction['n3']['payout']);
                }

                // Rule 6 | Both digit - Odd and Even.
                if ($gameSetupDataValue->gameName === 'BD_OddEven') {
                    $decimal = $this->bothDigit($stockHistoryDataValue, $precision[$stockHistoryDataKey]);

                    // Rules | Find child rules.
                    $decimal = array_count_values($decimal);
                    $rules = $this->getRules($gameSetupDataValue->rulesID);
                    foreach ($rules as $rule) {
                        // Debug mode on.
                        if ($debug) {
                            $this->info("\t" . $rule->name . ":(" . $rule['isMatched'] . '-' . $gameSetupDataValue->payout . ')');
                        }
                        if ($rule->name === 'BD_ODD') {
                            $tempRuleName = 'odd';
                        } elseif ($rule->name === 'BD_EVEN') {
                            $tempRuleName = 'even';
                        }
                        $DIGIT['both'][$tempRuleName] = $this->reckoningCipher('BOTH_DIGIT', $decimal, $rule['isMatched']);
                    }

                    // Calculating prediction for odd and even.
                    $prediction = $this->prediction('ODD_EVEN', $DIGIT['both'], $gameSetupDataValue->payout);
                    $this->columns[$stockHistoryDataKey]['BD_ODD'] = $this->organize($prediction['n1']['payout']);
                    $this->columns[$stockHistoryDataKey]['BD_EVEN'] = $this->organize($prediction['n2']['payout']);
                }

                // Rule 7 | Both digit - High, Middle and Low.
                if ($gameSetupDataValue->gameName === 'BD_HighMiddleLow') {
                    $decimal = $this->bothDigit($stockHistoryDataValue, $precision[$stockHistoryDataKey]);

                    // Rules | Find child rules.
                    $decimal = array_count_values($decimal);
                    $rules = $this->getRules($gameSetupDataValue->rulesID);
                    foreach ($rules as $rule) {
                        // Debug mode on.
                        if ($debug) {
                            $this->info("\t" . $rule->name . ":(" . $rule['isMatched'] . '-' . $gameSetupDataValue->payout . ')');
                        }
                        if ($rule->name === 'BD_HIGH') {
                            $tempRuleName = 'high';
                        } elseif ($rule->name === 'BD_MIDDLE') {
                            $tempRuleName = 'middle';
                        } elseif ($rule->name === 'BD_LOW') {
                            $tempRuleName = 'low';
                        }
                        $DIGIT['both'][$tempRuleName] = $this->reckoningCipher('BOTH_DIGIT', $decimal, $rule['isMatched']);
                    }

                    // Calculating prediction for Up, Middle and Low.
                    $prediction = $this->prediction("HIGH_MIDDLE_LOW", $DIGIT['both'], $gameSetupDataValue->payout);
                    $this->columns[$stockHistoryDataKey]['BD_HIGH'] = $this->organize($prediction['n1']['payout']);
                    $this->columns[$stockHistoryDataKey]['BD_MIDDLE'] = $this->organize($prediction['n2']['payout']);
                    $this->columns[$stockHistoryDataKey]['BD_LOW'] = $this->organize($prediction['n3']['payout']);
                }
            }
            $this->columns[$stockHistoryDataKey]['stockID'] = $stockHistoryDataKey;
            $progress->advance();
        }
        $progress->finish();
        $this->info(' Calculation completed!');

        // Inject the calculation into the database.
        $this->injectToDatabase();
        $this->info('Database updated.');

        return true;
    }

    // Public section - Script initialization begin.
    public function handle()
    {
        $debug = $this->option('debug');
        $START_TIME = microtime(true);
        try {
            $this->info('Script execution started.');
            $this->compute($debug);

            // Debug mode on.
            if ($debug) {
                print_r($this->columns);
                $this->info('---[ SCRIPT EXECUTION COMPLETED | EXECUTION TIME: ' . (microtime(true) - $START_TIME) . ' ]---');
            }
        } catch (Exception $e) {
            $msg = 'Error : ' . $e->getMessage() . "\n";
            $msg = $msg . $e->getTraceAsString() . "\n";

            $subject = "ERROR STACK TRACE => Crawler ($this->signature) : " . config('app.env');
            $to = config('constants.alert_mail_id');

            $this->error($e->getMessage());
            Log::debug($e->getMessage());
            MailJob::dispatch($to, $msg, $subject)->onQueue('medium');
        }
    }
}
