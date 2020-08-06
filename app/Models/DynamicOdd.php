<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Rule;
use Illuminate\Support\Facades\DB;

class DynamicOdd extends Model
{
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $table = 'dynamicOdd';
    protected $primaryKey = 'PID';

    protected $fillable = [
        'gameID', 'stockID', 'isActive',
        'FD_BIG', 'FD_SMALL', 'FD_ODD', 'FD_EVEN', 'FD_HIGH', 'FD_MIDDLE', 'FD_LOW', 'FD_0', 'FD_1', 'FD_2', 'FD_3', 'FD_4', 'FD_5', 'FD_6', 'FD_7', 'FD_8', 'FD_9',
        'LD_BIG', 'LD_SMALL', 'LD_ODD', 'LD_EVEN', 'LD_HIGH', 'LD_MIDDLE', 'LD_LOW', 'LD_0', 'LD_1', 'LD_2', 'LD_3', 'LD_4', 'LD_5', 'LD_6', 'LD_7', 'LD_8', 'LD_9',
        'TD_BIG', 'TD_SMALL', 'TD_TIE', 'TD_ODD', 'TD_EVEN', 'TD_HIGH', 'TD_MIDDLE', 'TD_LOW', 'TD_0', 'TD_1', 'TD_2', 'TD_3', 'TD_4', 'TD_5', 'TD_6', 'TD_7', 'TD_8', 'TD_9', 'TD_10', 'TD_11', 'TD_12', 'TD_13', 'TD_14', 'TD_15', 'TD_16', 'TD_17', 'TD_18', 'TD_19', 'TD_20', 'TD_21', 'TD_22', 'TD_23', 'TD_24', 'TD_25', 'TD_26', 'TD_27', 'TD_28', 'TD_29', 'TD_30', 'TD_31', 'TD_32', 'TD_33', 'TD_34', 'TD_35', 'TD_36', 'TD_37', 'TD_38', 'TD_39', 'TD_40', 'TD_41', 'TD_42', 'TD_43', 'TD_44', 'TD_45', 'TD_46', 'TD_47', 'TD_48', 'TD_49', 'TD_50', 'TD_51', 'TD_52', 'TD_53', 'TD_54', 'TD_55', 'TD_56', 'TD_57', 'TD_58', 'TD_59', 'TD_60', 'TD_61', 'TD_62', 'TD_63', 'TD_64', 'TD_65', 'TD_66', 'TD_67', 'TD_68', 'TD_69', 'TD_70', 'TD_71', 'TD_72', 'TD_73', 'TD_74', 'TD_75', 'TD_76', 'TD_77', 'TD_78', 'TD_79', 'TD_80', 'TD_81', 'TD_82', 'TD_83', 'TD_84', 'TD_85', 'TD_86', 'TD_87', 'TD_88', 'TD_89', 'TD_90', 'TD_91', 'TD_92', 'TD_93', 'TD_94', 'TD_95', 'TD_96', 'TD_97', 'TD_98', 'TD_99',
        'BD_BIG', 'BD_SMALL', 'BD_ODD', 'BD_EVEN', 'BD_HIGH', 'BD_MIDDLE', 'BD_LOW', 'BD_0', 'BD_1', 'BD_2', 'BD_3', 'BD_4', 'BD_5', 'BD_6', 'BD_7', 'BD_8', 'BD_9', 'BD_10', 'BD_11', 'BD_12', 'BD_13', 'BD_14', 'BD_15', 'BD_16', 'BD_17', 'BD_18', 'BD_TIE',
        'createdAt', 'updatedAt'
    ];


    public function dynamicOddByRule($gameID, $ruleID)
    {
        $ruleName = Rule::select('name')->where('PID', $ruleID)->where('isActive', 'active')->get();
        if ($ruleName->count(DB::raw('1')) > 0) {
            return $this->select($ruleName[0]->name . " as payout")->where('gameID', $gameID)->get();
        } else {
            return Res::notFound([], 'Rule id does not exist or is inActive'); //do we need different response for both.
        }
    }

    public function dynamicOddByGame($gameID)
    {
        return $this->select('game.stockID', 'game.UUID', 'FD_BIG', 'FD_SMALL', 'FD_ODD', 'FD_EVEN', 'FD_HIGH', 'FD_MIDDLE', 'FD_LOW', 'FD_0', 'FD_1', 'FD_2', 'FD_3', 'FD_4', 'FD_5', 'FD_6', 'FD_7', 'FD_8', 'FD_9', 'LD_BIG', 'LD_SMALL', 'LD_ODD', 'LD_EVEN', 'LD_HIGH', 'LD_MIDDLE', 'LD_LOW', 'LD_0', 'LD_1', 'LD_2', 'LD_3', 'LD_4', 'LD_5', 'LD_6', 'LD_7', 'LD_8', 'LD_9', 'TD_BIG', 'TD_SMALL', 'TD_TIE', 'TD_ODD', 'TD_EVEN', 'TD_HIGH', 'TD_MIDDLE', 'TD_LOW', 'TD_0', 'TD_1', 'TD_2', 'TD_3', 'TD_4', 'TD_5', 'TD_6', 'TD_7', 'TD_8', 'TD_9', 'TD_10', 'TD_11', 'TD_12', 'TD_13', 'TD_14', 'TD_15', 'TD_16', 'TD_17', 'TD_18', 'TD_19', 'TD_20', 'TD_21', 'TD_22', 'TD_23', 'TD_24', 'TD_25', 'TD_26', 'TD_27', 'TD_28', 'TD_29', 'TD_30', 'TD_31', 'TD_32', 'TD_33', 'TD_34', 'TD_35', 'TD_36', 'TD_37', 'TD_38', 'TD_39', 'TD_40', 'TD_41', 'TD_42', 'TD_43', 'TD_44', 'TD_45', 'TD_46', 'TD_47', 'TD_48', 'TD_49', 'TD_50', 'TD_51', 'TD_52', 'TD_53', 'TD_54', 'TD_55', 'TD_56', 'TD_57', 'TD_58', 'TD_59', 'TD_60', 'TD_61', 'TD_62', 'TD_63', 'TD_64', 'TD_65', 'TD_66', 'TD_67', 'TD_68', 'TD_69', 'TD_70', 'TD_71', 'TD_72', 'TD_73', 'TD_74', 'TD_75', 'TD_76', 'TD_77', 'TD_78', 'TD_79', 'TD_80', 'TD_81', 'TD_82', 'TD_83', 'TD_84', 'TD_85', 'TD_86', 'TD_87', 'TD_88', 'TD_89', 'TD_90', 'TD_91', 'TD_92', 'TD_93', 'TD_94', 'TD_95', 'TD_96', 'TD_97', 'TD_98', 'TD_99', 'BD_BIG', 'BD_SMALL', 'BD_ODD', 'BD_EVEN', 'BD_HIGH', 'BD_MIDDLE', 'BD_LOW', 'BD_0', 'BD_1', 'BD_2', 'BD_3', 'BD_4', 'BD_5', 'BD_6', 'BD_7', 'BD_8', 'BD_9', 'BD_10', 'BD_11', 'BD_12', 'BD_13', 'BD_14', 'BD_15', 'BD_16', 'BD_17', 'BD_18', 'BD_TIE')
            ->join('game', 'dynamicOdd.gameID', '=', 'game.PID')
            ->where('gameID', $gameID)
            ->get();
    }
}
