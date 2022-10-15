<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use SWeb3\SWeb3;
use SWeb3\SWeb3_Contract;
use SWeb3\Utils; 

use App\Http\Requests\API\StakeRequest;
use App\Http\Requests\API\UnstakeRequest;
use App\Http\Requests\API\StakeListRequest;
use App\Http\Requests\API\SendRequest;
use App\Http\Requests\API\TransactionStatusRequest;

use App\Services\User\UserService;

use Carbon\Carbon;

class FixedController extends Controller
{
    const HACK_TOKEN_ADDRESS = '0x12E95AfaE2FB674c769e5E1b089cdD39d97ff262';
    const HACK_INTEREST_TOKEN_ADDRESS = '0x19a78C8c13893fD549aAAFD751c292C679FdF441';
    const STAKING_ADDRESS = '0xd11E4e2fc55E7a3205871437160EC42C580f028d';
    
    private $batchInfo = [];
    
    public function overview(){
        
        $contracts = [];
        
        $sweb3 = new SWeb3('https://data-seed-prebsc-1-s2.binance.org:8545');
        $sweb3->chainId = 0x61;//bsc testnet 
        $sweb3->batch(true);
        
        $totalFixedToken = $this->callContractBatch($sweb3, 'HackToken',self::HACK_TOKEN_ADDRESS, 'totalSupply', []);
        $fixedTokenSymbol = $this->callContractBatch($sweb3, 'HackToken',self::HACK_TOKEN_ADDRESS, 'symbol', []);
        $fixedTokenName = $this->callContractBatch($sweb3, 'HackToken',self::HACK_TOKEN_ADDRESS, 'name', []);
        
        $totalStakedFixedToken = $this->callContractBatch($sweb3, 'TokenStaking',self::STAKING_ADDRESS, 'totalAmountStaked', []);
        
        $result = $sweb3->executeBatch();
        
        $data = [];
        
        foreach($this->batchInfo as $key => $info){
            $data[$info['name']][$info['function']] = ($info['contract'])->DecodeData($info['function'], $result[$key]->result);
        }
        
        $returnData = [
            'token_total_supply' => $this->dividePrecision($data['HackToken']['totalSupply']->result, 8),
            'token_symbol' => $data['HackToken']['symbol']->elem_1,
            'token_name' => $data['HackToken']['name']->elem_1,
            'total_staked_token' => $this->dividePrecision($data['TokenStaking']['totalAmountStaked']->result, 8),
        ];
        
        return httpResponse()::httpSuccess($returnData);
    }
    
    public function stakeOverview(){
        
        $userWallet = $this->getUserWallet();
        
        $contracts = [];
        
        $sweb3 = new SWeb3('https://data-seed-prebsc-1-s2.binance.org:8545');
        $sweb3->chainId = 0x61;//bsc testnet 
        $sweb3->batch(true);
        
        $this->callContractBatch($sweb3, 'TokenStaking',self::STAKING_ADDRESS, 'buybackRate', []);
        $this->callContractBatch($sweb3, 'TokenStaking',self::STAKING_ADDRESS, 'interestRate', []);
        $this->callContractBatch($sweb3, 'TokenStaking',self::STAKING_ADDRESS, 'totalAmountStaked', []);
        $this->callContractBatch($sweb3, 'TokenStaking',self::STAKING_ADDRESS, 'getStakeCount', [$userWallet['address']]);
        $this->callContractBatch($sweb3, 'HackToken',self::HACK_TOKEN_ADDRESS, 'allowance', [$userWallet['address'], self::STAKING_ADDRESS]);
        $this->callContractBatch($sweb3, 'HackInterestToken',self::HACK_INTEREST_TOKEN_ADDRESS, 'allowance', [$userWallet['address'], self::STAKING_ADDRESS]);
        $this->callContractBatch($sweb3, 'TokenStaking',self::STAKING_ADDRESS, 'getTotalRedeemableAmount', [$userWallet['address']]);
        $this->callContractBatch($sweb3, 'TokenStaking',self::STAKING_ADDRESS, 'userMapping', [$userWallet['address']]);
        
        $result = $sweb3->executeBatch();
        
        $data = [];
        
        foreach($this->batchInfo as $key => $info){
            $data[$info['name']][$info['function']] = ($info['contract'])->DecodeData($info['function'], $result[$key]->result);
        }
        
        $totalStaked = $this->dividePrecision($data['TokenStaking']['userMapping']->totalInitialAmount, 8);
        $totalBalance = $this->dividePrecision($data['TokenStaking']['userMapping']->totalBalanceAmount, 8);
        $totalBuyback = $this->dividePrecision($data['TokenStaking']['userMapping']->totalBuybackAmount, 8);
        $totalInterest = $this->dividePrecision($data['TokenStaking']['userMapping']->totalInterestAmount, 8);
        $redeemableBalance = $this->dividePrecision($data['TokenStaking']['getTotalRedeemableAmount']->tuple_1->redeemableAmount, 8);
        
        $contract = [
            "buyback_rate_percentage" => (int) $data['TokenStaking']['buybackRate']->result->value,
            "interest_rate_percentage" => (int) $data['TokenStaking']['interestRate']->result->value,
            "total_amount_staked" => $this->dividePrecision($data['TokenStaking']['totalAmountStaked']->result, 8),
            "userDetails" => [
                "stake_count" => (int) $data['TokenStaking']['getStakeCount']->result->value,
                "staked" => $totalStaked,
                "balance" => $totalBalance,
                "buyback" => $totalBuyback,
                "interest" => $totalInterest,
                "total_redeemable_amount" => $redeemableBalance,
                "approve_stake" => $data['HackToken']['allowance']->result->compare(Utils::toBn(0)),
                "approve_claim" => $data['HackInterestToken']['allowance']->result->compare(Utils::toBn(0)),
            ],
        ];
        
        array_push($contracts, $contract);
            
        return httpResponse()::httpSuccess(["contracts" => $contracts]);
    }
    
    public function personalOverview(){
        
        $userWallet = $this->getUserWallet();
        
        $contracts = [];
        
        $sweb3 = new SWeb3('https://data-seed-prebsc-1-s2.binance.org:8545');
        $sweb3->chainId = 0x61;//bsc testnet 
        $sweb3->batch(true);
        
        $this->callContractBatch($sweb3, 'HackToken',self::HACK_TOKEN_ADDRESS, 'balanceOf', [$userWallet['address']]);
        $this->callContractBatch($sweb3, 'TokenStaking',self::STAKING_ADDRESS, 'getTotalRedeemableAmount', [$userWallet['address']]);
        
        $result = $sweb3->executeBatch();
        
        $data = [];
        
        foreach($this->batchInfo as $key => $info){
            $data[$info['name']][$info['function']] = ($info['contract'])->DecodeData($info['function'], $result[$key]->result);
        }
        
        $tokenBalance = $this->dividePrecision($data['HackToken']['balanceOf']->result, 8);
        $redeemableBalance = $this->dividePrecision($data['TokenStaking']['getTotalRedeemableAmount']->tuple_1->redeemableAmount, 8);
        
        $contract = [
            "fixed_token_balance" => $tokenBalance,
            "total_redeemable_amount" => $redeemableBalance,
        ];
        
        array_push($contracts, $contract);
        
        return httpResponse()::httpSuccess(["contracts" => $contracts]);
    }
    
    public function getStakes(StakeListRequest $request){
        
        $request = $request->validated();
        
        $userWallet = $this->getUserWallet();
        
        $stakes = [];
        
        $stakingCount = $this->callContract('TokenStaking',self::STAKING_ADDRESS, 'getStakeCount', [$userWallet['address']]);
        $stakingCount = (int) $stakingCount->result->value;
        
        $limit = $request['limit'];
        $start = $request['start'];
        
        if($start+$limit > $stakingCount){
            $count = $stakingCount;
        }else{
            $count = $start+$limit;
        }
        
        $sweb3 = new SWeb3('https://data-seed-prebsc-1-s2.binance.org:8545');
        $sweb3->chainId = 0x61;//bsc testnet 
        $sweb3->batch(true);
        
        for($i = $start; $i< $count; $i++){
            $this->callContractBatchLoop($i, $sweb3, 'TokenStaking',self::STAKING_ADDRESS, 'stakeMapping', [$userWallet['address'], (string)$i]);
        }

        $result = $sweb3->executeBatch();
        
        $data = [];
        
        foreach($this->batchInfo as $key => $info){
            $data[$info['name']][$info['function']][$info['key']] = ($info['contract'])->DecodeData($info['function'], $result[$key]->result);
        }
        
        if(sizeof($data) > 0){
            foreach($data['TokenStaking']['stakeMapping'] as $key => $stake){
                
                $item = [];
                $item['key'] = (string)$key;
                $item['staked_id'] = $key;
                $item['staked_date'] = Carbon::createFromTimestamp($stake->dateStaked->value)->toDateTimeString(); 
                $item['initial_staked_amount'] = $this->dividePrecision($stake->initialAmount, 8);
                $item['interest_amount'] = $this->dividePrecision($stake->interestAmount, 8);
                $item['buyback_amount'] = $this->dividePrecision($stake->buybackAmount, 8);
                $item['redeemed_amount'] = $this->dividePrecision($stake->redeemedAmount, 8);
                $item['balance_amount'] = $this->dividePrecision($stake->balanceAmount, 8);
                
                array_push($stakes, $item);
            }
        }
            
        return httpResponse()::httpSuccess(["stake_count" => $stakingCount, "stakes" => $stakes]);
    }
    
    public function getTransactionStatus(TransactionStatusRequest $request){
        
        $request = $request->validated();
        
        $sweb3 = new SWeb3('https://data-seed-prebsc-1-s2.binance.org:8545');
        
        $res = $sweb3->call('eth_getTransactionReceipt', [$request['transaction_hash']]);
        
        if(!is_null($res->result)){
            $result = Utils::stripZero($res->result->status);
            return httpResponse()::httpSuccess(['status' => $result]);
        }else{
            throwApiErrorException([
                    'msg' => 'Invalid transaction hash.'
                ]
            );
        }
        
    }
    
    public function setupWallet(){
        
        $userId =  auth('sanctum')->user()->id ?? 0;
        
        if($userId > 0){
            $userService = new UserService();
            
            return $userService->updateWalletInfo($userId);
        }else{
            throwApiErrorException([
                    'msg' => 'Invalid user.'
                ]
            );
        }
    }
    
    public function approveStakeStatus(){
        $userWallet = $this->getUserWallet();
        
        $approveStake = $this->callContract('HackToken',self::HACK_TOKEN_ADDRESS, 'allowance', [$userWallet['address'], self::STAKING_ADDRESS]);
        
        return httpResponse()::httpSuccess(["approve_stake" => $approveStake->result->compare(Utils::toBn(0))]);
    }
    
    public function approveClaimStatus(){
        $userWallet = $this->getUserWallet();
        
        $approveClaim = $this->callContract('HackInterestToken',self::HACK_INTEREST_TOKEN_ADDRESS, 'allowance', [$userWallet['address'], self::STAKING_ADDRESS]);
        
        return httpResponse()::httpSuccess(["approve_claim" => $approveClaim->result->compare(Utils::toBn(0))]);
    }
    
    public function approveStake(){
        
        $userWallet = $this->getUserWallet();
       
        $check = $this->callContract('HackToken',self::HACK_TOKEN_ADDRESS, 'allowance', [$userWallet['address'], self::STAKING_ADDRESS]);
        $checkVal = $check->result->value;
        
        if(gmp_cmp($checkVal, 0) <= 0){
            $approveAmount = Utils::hexToBn('ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff');
            $result = $this->sendContract($userWallet, 'HackToken', self::HACK_TOKEN_ADDRESS, 'increaseAllowance', [self::STAKING_ADDRESS, $approveAmount]);
            
            return httpResponse()::httpSuccess($result);
        }else{
            throwApiErrorException([
                    'msg' => 'Already approved.'
                ]
            );
        }
        
    }
    
    public function approveClaim(){

        $userWallet = $this->getUserWallet();
    
        $check = $this->callContract('HackInterestToken',self::HACK_INTEREST_TOKEN_ADDRESS, 'allowance', [$userWallet['address'], self::STAKING_ADDRESS]);
        $checkVal = $check->result->value;
        
        if(gmp_cmp($checkVal, 0) <= 0){
            $approveAmount = Utils::hexToBn('ffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff');
            $result = $this->sendContract($userWallet, 'HackInterestToken', self::HACK_INTEREST_TOKEN_ADDRESS, 'increaseAllowance', [self::STAKING_ADDRESS, $approveAmount]);
            
            return httpResponse()::httpSuccess($result);
        }else{
            throwApiErrorException([
                    'msg' => 'Already approved.'
                ]
            );
        }
    }
    
    public function stake(StakeRequest $request){
        
        $request = $request->validated();
        $userWallet = $this->getUserWallet();
        
        $checkApprove = $this->callContract('HackToken',self::HACK_TOKEN_ADDRESS, 'allowance', [$userWallet['address'], self::STAKING_ADDRESS]);
        $approve = $checkApprove->result->value;
        
        if(gmp_cmp($approve, 0) > 0){
            $checkDecimals = $this->callContract('HackToken',self::HACK_TOKEN_ADDRESS, 'decimals', []);
            $decimals = (int) $checkDecimals->result->value;
            
            $precision = Utils::toBn(pow(10,$decimals));
            
            if(!str_contains($request['amount'], '.')){
                $amount = Utils::toBn($request['amount']);
                $stakeAmount = $amount->multiply($precision);
            }else{
                $amountArr = explode('.', $request['amount']);
                $remainderDecimals = strlen($amountArr[1]);
                $newPrecision = Utils::toBn(pow(10,$decimals-$remainderDecimals));
                $whole = Utils::toBn($amountArr[0])->multiply($precision);
                $remainder = Utils::toBn($amountArr[1])->multiply($newPrecision);
                
                $stakeAmount = $whole->add($remainder);
            }
            $result = $this->sendContract($userWallet, 'TokenStaking', self::STAKING_ADDRESS, 'deposit', [$stakeAmount]);
            
            return httpResponse()::httpSuccess($result);
        }else{
            throwApiErrorException([
                    'msg' => 'Not approved.'
                ]
            );
        }
    }
    
    public function unstake(UnstakeRequest $request){
        
        $request = $request->validated();
        
        $stakeId = $request['stake_id'];
        
        $userWallet = $this->getUserWallet();
        
        $check = $this->callContract('TokenStaking',self::STAKING_ADDRESS, 'getStakeCount', [$userWallet['address']]);
        $checkVal = $check->result->value;
        
        if($checkVal > $stakeId){
            $result = $this->sendContract($userWallet, 'TokenStaking', self::STAKING_ADDRESS, 'unstake', [(string)$stakeId]);
            
            return httpResponse()::httpSuccess($result);
        }else{
            throwApiErrorException([
                    'msg' => 'No available stake.'
                ]
            );
        }
    }
    
    public function claim(){
        
        $userWallet = $this->getUserWallet();
        
        $check = $this->callContract('TokenStaking',self::STAKING_ADDRESS, 'getTotalRedeemableAmount', [$userWallet['address']]);
        $checkVal = $check->tuple_1->redeemableAmount->value;
        
        if(gmp_cmp($checkVal, 0) > 0){
            $result = $this->sendContract($userWallet, 'TokenStaking', self::STAKING_ADDRESS, 'redeemInterest', []);
            
            return httpResponse()::httpSuccess($result);
        }else{
            throwApiErrorException([
                    'msg' => 'No available claim.'
                ]
            );
        }
    }
    
    public function send(SendRequest $request){
        $request = $request->validated();
        
        $userWallet = $this->getUserWallet();
        
        $checkDecimals = $this->callContract('HackToken',self::HACK_TOKEN_ADDRESS, 'decimals', []);
        $decimals = (int) $checkDecimals->result->value;
        
        $precision = Utils::toBn(pow(10,$decimals));
        
        if(!str_contains($request['amount'], '.')){
            $amount = Utils::toBn($request['amount']);
            $sendAmount = $amount->multiply($precision);
        }else{
            $amountArr = explode('.', $request['amount']);
            $remainderDecimals = strlen($amountArr[1]);
            $newPrecision = Utils::toBn(pow(10,$decimals-$remainderDecimals));
            $whole = Utils::toBn($amountArr[0])->multiply($precision);
            $remainder = Utils::toBn($amountArr[1])->multiply($newPrecision);
            
            $sendAmount = $whole->add($remainder);
        }
        
        $result = $this->sendContract($userWallet, 'HackToken', self::HACK_TOKEN_ADDRESS, 'transfer', [$request['to_address'], $sendAmount]);
            
        return httpResponse()::httpSuccess($result);
    }
    
    private function callContractBatch($sweb3, $json, $address, $function, $inputs = []){
        //initialize SWeb3 main object
        
        $sweb3->chainId = 0x61;//bsc testnet 
        
        $jsonString = file_get_contents(base_path('resources/abi/'.$json.'.json'));
        $contract = new SWeb3_Contract($sweb3, $address, $jsonString);
        
        $contract->call($function, $inputs);
        
        array_push($this->batchInfo, ['contract' => $contract, "name" => $json, "function" => $function]);
    }
    
    private function callContractBatchLoop($key, $sweb3, $json, $address, $function, $inputs = []){
        //initialize SWeb3 main object
        
        $sweb3->chainId = 0x61;//bsc testnet 
        
        $jsonString = file_get_contents(base_path('resources/abi/'.$json.'.json'));
        $contract = new SWeb3_Contract($sweb3, $address, $jsonString);
        
        $contract->call($function, $inputs);
        
        array_push($this->batchInfo, ['contract' => $contract, "name" => $json, "function" => $function, "key" => $key]);
    }
    
    private function callContract($json, $address, $function, $inputs = []){
        //initialize SWeb3 main object
        $sweb3 = new SWeb3('https://data-seed-prebsc-1-s2.binance.org:8545');
        
        $sweb3->chainId = 0x61;//bsc testnet 
        
        $jsonString = file_get_contents(base_path('resources/abi/'.$json.'.json'));
        $contract = new SWeb3_Contract($sweb3, $address, $jsonString);
        
        $result = $contract->call($function, $inputs);
        
        return $result;
    }
    
    private function sendContract($user, $json, $address, $function, $inputs = []){
        //initialize SWeb3 main object
        $sweb3 = new SWeb3('https://data-seed-prebsc-1-s2.binance.org:8545');
        
        //optional if not sending transactions
        $from_address = $user['address'];
        $from_address_private_key = $user['private_key'];
        $sweb3->setPersonalData($from_address, $from_address_private_key);
        $sweb3->chainId = 0x61;//bsc testnet 
        // $sweb3->gasPrice = $sweb3->getGasPrice();
        
        $jsonString = file_get_contents(base_path('resources/abi/'.$json.'.json'));
        $contract = new SWeb3_Contract($sweb3, $address, $jsonString);
        
        $extra_data = ['from' => $from_address, 'nonce' => $sweb3->personal->getNonce(), 'gasPrice' => $sweb3->getGasPrice()];
        $result = $contract->send($function, $inputs, $extra_data);
        
        return $result;
    }
    
    private function dividePrecision($value, $decimals = 18){
        $precision = Utils::toBn(pow(10,$decimals));
        
        list($quotient, $remainder) = $value->divide($precision);
        
        return $quotient->value.'.'.$remainder->value;
    }
    
    private function getUserWallet(){
        
        $userService = new UserService();
        
        $userId =  auth('sanctum')->user()->id ?? 0;
        
        if($userId > 0){
            $user = $userService->index($userId);
            $userInfo = $user->info;
            
            if(is_null($userInfo->wallet_address) || $userInfo->wallet_address == ''){
                throwApiErrorException([
                    'msg' => 'Please setup wallet first.'
                    ]
                );
            }
            
            return [
                'address' => $userInfo->wallet_address,
                'private_key' => $userInfo->private_key,
            ];
        }else{
            throwApiErrorException([
                    'msg' => 'Invalid user.'
                ]
            );
        }
    }
}
