<?php namespace Model\Travio\Elements;

use Model\ORM\Element;
use Model\Payments\PaymentsOrderInterface;

class TravioOrderBase extends Element implements PaymentsOrderInterface
{
	public static ?string $table = 'travio_orders';
	public ?array $response = null;

	protected function afterLoad(array $options)
	{
		$this->response = json_decode($this['response'] ?: '', true);
	}

	public function afterSave($previous_data, array $saving)
	{
		$this->response = json_decode($this['response'] ?: '', true);
	}

	public function getGateway(): ?string
	{
		return $this['gateway'];
	}

	public function getPrice(): float
	{
		return $this['amount'];
	}

	public function getShipping(): float
	{
		return 0;
	}

	public function getOrderDescription(): string
	{
		return 'Pratica #' . $this['reference'] . ' - ' . APP_NAME;
	}

	public function getInvoiceId(): ?string
	{
		return $this['reference'];
	}

	public function isPaid(): bool
	{
		return (bool)$this['paid'];
	}

	public function markAsPaid()
	{
		$this->save(['paid' => date('Y-m-d H:i:s')]);

		if ($this['is_first_payment']) {
			$this->confirm($this['amount']);
		} else {
			$this->model->_Travio->request('pay', [
				'reference' => $this['reference'],
				'amount' => (float)$this['amount'],
				'payment_reference' => (string)$this['id'],
			]);
		}
	}

	public function confirm(?float $paid = null)
	{
		$response = $this->model->_Travio->confirmOrder($this['reference'], $paid, $this['id']);
		$this->save(['initial_status' => $response['booking-status']]);
	}
}
