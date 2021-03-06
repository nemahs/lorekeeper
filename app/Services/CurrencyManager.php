<?php namespace App\Services;

use Carbon\Carbon;
use App\Services\Service;

use DB;
use Config;
use Notifications;

use App\Models\User\User;
use App\Models\Currency\Currency;
use App\Models\User\UserCurrency;

class CurrencyManager extends Service
{
    public function grantUserCurrencies($data, $staff)
    {
        DB::beginTransaction();

        try {
            if($data['quantity'] == 0) throw new \Exception("Please enter a non-zero quantity.");

            // Process names
            $users = User::whereIn('name', explode(',', str_replace(' ', '', $data['names'])))->get();
            if(!count($users)) throw new \Exception("No valid users found.");

            // Process currency
            $currency = Currency::find($data['currency_id']);
            if(!$currency) throw new \Exception("Invalid currency selected.");
            if(!$currency->is_user_owned) throw new \Exception("This currency cannot be held by users.");

            if($data['quantity'] < 0) 
                foreach($users as $user) {
                    $this->debitCurrency($staff, $user, 'Staff Removal', $data['data'], $currency, -$data['quantity']);
                    Notifications::create('CURRENCY_REMOVAL', $user, [
                        'currency_name' => $currency->name,
                        'currency_quantity' => -$data['quantity'],
                        'sender_url' => $staff->url,
                        'sender_name' => $staff->name
                    ]);
                }
            else
                foreach($users as $user) {
                    $this->creditCurrency($staff, $user, 'Staff Grant', $data['data'], $currency, $data['quantity']);
                    Notifications::create('CURRENCY_GRANT', $user, [
                        'currency_name' => $currency->name,
                        'currency_quantity' => $data['quantity'],
                        'sender_url' => $staff->url,
                        'sender_name' => $staff->name
                    ]);
                }

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    // NOTE: currently only transfers between users.
    public function transferCurrency($sender, $recipient, $currency, $quantity)
    {
        DB::beginTransaction();

        try {
            if(!$recipient) throw new \Exception("Invalid recipient selected.");
            if(!$recipient->hasAlias) throw new \Exception("Cannot transfer currency to a non-verified member.");
            if(!$currency) throw new \Exception("Invalid currency selected.");
            if($quantity <= 0) throw new \Exception("Invalid quantity entered.");


            if($this->debitCurrency($sender, $recipient, null, null, $currency, $quantity) &&
            $this->creditCurrency($sender, $recipient, null, null, $currency, $quantity)) 
            {
                $this->createLog($sender->id, $sender->logType, $recipient->id, $recipient->logType, 'User Transfer', null, $currency->id, $quantity);
                
                Notifications::create('CURRENCY_TRANSFER', $recipient, [
                    'currency_name' => $currency->name,
                    'currency_quantity' => $quantity,
                    'sender_url' => $sender->url,
                    'sender_name' => $sender->name
                ]);
                return $this->commitReturn(true);
            }
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    public function creditCurrency($sender, $recipient, $type, $data, $currency, $quantity)
    {
        DB::beginTransaction();

        try {
            $record = UserCurrency::where('user_id', $recipient->id)->where('currency_id', $currency->id)->first();
            if($record) {
                // Laravel doesn't support composite primary keys, so directly updating the DB row here
                DB::table('user_currencies')->where('user_id', $recipient->id)->where('currency_id', $currency->id)->update(['quantity' => $record->quantity + $quantity]);
            }
            else {
                $record = UserCurrency::create(['user_id' => $recipient->id, 'currency_id' => $currency->id, 'quantity' => $quantity]);
            }
            if($type && !$this->createLog($sender ? $sender->id : null, $sender ? $sender->logType : null, $recipient->id, $recipient->logType, $type, $data, $currency->id, $quantity)) throw new \Exception("Failed to create log.");

            return $this->commitReturn(true);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    public function debitCurrency($sender, $recipient, $type, $data, $currency, $quantity)
    {
        DB::beginTransaction();

        try {
            $record = UserCurrency::where('user_id', $sender->id)->where('currency_id', $currency->id)->first();
            if(!$record || $record->quantity < $quantity) throw new \Exception("Not enough ".$currency->name." to carry out this action.");

            // Laravel doesn't support composite primary keys, so directly updating the DB row here
            DB::table('user_currencies')->where('user_id', $sender->id)->where('currency_id', $currency->id)->update(['quantity' => $record->quantity - $quantity]);

            if($type && !$this->createLog($sender ? $sender->id : null, $sender ? $sender->logType : null, $recipient->id, $recipient->logType, $type, $data, $currency->id, $quantity)) throw new \Exception("Failed to create log.");

            return $this->commitReturn($currency);
        } catch(\Exception $e) { 
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    public function createLog($senderId, $senderType, $recipientId, $recipientType, $type, $data, $currencyId, $quantity)
    {
        return DB::table('currencies_log')->insert(
            [
                'sender_id' => $senderId,
                'sender_type' => $senderType,
                'recipient_id' => $recipientId,
                'recipient_type' => $recipientType,
                'log' => $type . ($data ? ' (' . $data . ')' : ''),
                'log_type' => $type,
                'data' => $data, // this should be just a string
                'currency_id' => $currencyId,
                'quantity' => $quantity,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        );
    }
}