<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'third_party/autoload.php';

class Stripe_payments extends CI_Model
{

    protected $private_key;
    public $message = '';
    public $code;
    public $error = FALSE;

    public function __construct()
    {
        parent::__construct();
        $this->config->load('payment_gateways');
        $this->private_key = $this->config->item('stripe_secret_key');
        $this->set_api_key();
    }

    function set_api_key()
    {
        \Stripe\Stripe::setApiKey($this->private_key);
    }

    public function init($config = array())
    {
        if (isset($config['private_key'])) {
            $this->private_key = $config['private_key'];
        }
        $this->set_api_key();

    }

    public function get_balance()
    {
        try {
            $bal = \Stripe\Balance::retrieve();
            return array('mode' => ($bal->livemode ? $bal->livemode : 'Test'), 'pending_amount' => ($bal->pending[0]->amount / 100), 'pending_currency' => strtoupper($bal->pending[0]->currency), 'available_amount' => ($bal->available[0]->amount / 100), 'available_currency' => strtoupper($bal->available[0]->currency));
        } catch (Exception $e) {
            $this->error = TRUE;
            $this->message = $e->getMessage();
            $this->code = $e->getCode();
            //return FALSE;
            return array('error' => TRUE, 'code' => $this->code, 'message' => $this->message);
        }
    }

    public function create_card_token($card_info)
    {
        if (isset($card_info['number'])) {
            $card_info = array('card' => $card_info);
        }
        try {
            $card = \Stripe\Token::create($card_info);
            return $card;
        } catch (Exception $e) {
            $this->error = TRUE;
            $this->message = $e->getMessage();
            $this->code = $e->getCode();
            //return FALSE;
            return array('error' => TRUE, 'code' => $this->code, 'message' => $this->message);
        }
    }

    public function get_transaction($transaction_id)
    {
        try {
            $ch = \Stripe\Charge::retrieve($transaction_id);
            return $ch;
        } catch (Exception $e) {
            $this->error = TRUE;
            $this->message = $e->getMessage();
            $this->code = $e->getCode();
            return FALSE;
        }
    }

    public function get_all_transactions($num_charges = 100, $offset = 0)
    {
        try {
            $ch = \Stripe\Charge::all(array(
                'limit' => $num_charges,
            ));
            $data['error'] = FALSE;
            $raw_data = array();
            foreach ($ch->data as $record) {
                $raw_data[] = $this->charge_to_array($record);
            }
            $data['data'] = $raw_data;
            return $data;
        } catch (Exception $e) {
            $this->error = TRUE;
            $this->message = $e->getMessage();
            $this->code = $e->getCode();
            return FALSE;
        }
    }

    public function count_all_transactions()
    {
        $charges = $this->get_all_transactions();
        return count($charges);
    }

    public function insert($token, $description, $amount, $currency)
    {
        try {
            $charge = \Stripe\Charge::create(array(
                'amount' => $amount,
                'currency' => $currency,
                'source' => $token,
                'description' => $description
            ));
            return $charge;
        } catch (Exception $e) {
            $this->error = TRUE;
            $this->message = $e->getMessage();
            $this->code = $e->getCode();
            //return FALSE;
            return array('error' => TRUE, 'code' => $this->code, 'message' => $this->message);
        }
    }

    function charge($token, $description, $amount, $currency)
    {
        return $this->insert($token, $description, $amount, $currency);
    }

    public function insert_many($data)
    {
        $ids = array();

        foreach ($data as $row) {
            $ids[] = $this->insert($row['token'], $row['description'], $row['amount'], $row['currency']);
        }
        return $ids;
    }

    public function get_limit($limit, $offset = 0)
    {
        return $this->get_all_transactions($limit, $offset);
    }

    function refund($transaction_id, $amount = 'all')
    {
        $transaction = $this->get_transaction($transaction_id);
        if ($transaction) {
            if ($amount == 'all') {
                $amount = $transaction->amount;
            }
            try {
                $response = \Stripe\Refund::create(array(
                    'charge' => $transaction_id,
                    'amount' => $amount
                ));
                return $response;
            } catch (Exception $e) {
                $this->error = TRUE;
                $this->message = $e->getMessage();
                $this->code = $e->getCode();
                return FALSE;
            }
        } else {
            $this->error = TRUE;
            return FALSE;
        }
    }

    function charge_to_array($charge)
    {
        $card = null;
        if (isset($charge->source) && is_object($charge->source)) {
            $card = $charge->source;
        } elseif (isset($charge->payment_method_details->card)) {
            $card = $charge->payment_method_details->card;
        }
        $fee = 0;
        if (isset($charge->balance_transaction) && is_object($charge->balance_transaction) && isset($charge->balance_transaction->fee)) {
            $fee = $charge->balance_transaction->fee;
        } elseif (isset($charge->fee)) {
            $fee = $charge->fee;
        }
        $data = array(
            'id' => $charge->id,
            'invoice' => isset($charge->invoice) ? $charge->invoice : null,
            'card' => $card ? $this->card_to_array($card) : array(),
            'livemode' => $charge->livemode,
            'amount' => $charge->amount,
            'failure_message' => isset($charge->failure_message) ? $charge->failure_message : null,
            'fee' => $fee,
            'currency' => $charge->currency,
            'paid' => $charge->paid,
            'description' => $charge->description,
            'disputed' => $charge->disputed,
            'object' => $charge->object,
            'refunded' => $charge->refunded,
            'created' => date('Y-m-d H:i:s', $charge->created),
            'customer' => isset($charge->customer) ? $charge->customer : null,
            'amount_refunded' => $charge->amount_refunded,
        );
        return $data;
    }

    function card_to_array($card)
    {
        $data = array(
            'address_country' => isset($card->address_country) ? $card->address_country : null,
            'type' => isset($card->type) ? $card->type : (isset($card->brand) ? $card->brand : null),
            'address_zip_check' => isset($card->address_zip_check) ? $card->address_zip_check : null,
            'fingerprint' => isset($card->fingerprint) ? $card->fingerprint : null,
            'address_state' => isset($card->address_state) ? $card->address_state : null,
            'exp_month' => isset($card->exp_month) ? $card->exp_month : null,
            'address_line1_check' => isset($card->address_line1_check) ? $card->address_line1_check : null,
            'country' => isset($card->country) ? $card->country : null,
            'last4' => isset($card->last4) ? $card->last4 : null,
            'exp_year' => isset($card->exp_year) ? $card->exp_year : null,
            'address_zip' => isset($card->address_zip) ? $card->address_zip : null,
            'object' => isset($card->object) ? $card->object : null,
            'address_line1' => isset($card->address_line1) ? $card->address_line1 : null,
            'name' => isset($card->name) ? $card->name : null,
            'address_line2' => isset($card->address_line2) ? $card->address_line2 : null,
            'id' => isset($card->id) ? $card->id : null,
            'cvc_check' => isset($card->cvc_check) ? $card->cvc_check : (isset($card->checks->cvc_check) ? $card->checks->cvc_check : null),
        );
        return $data;
    }

}