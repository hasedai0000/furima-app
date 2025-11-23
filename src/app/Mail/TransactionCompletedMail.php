<?php

namespace App\Mail;

use App\Domain\Transaction\Entities\Transaction as TransactionEntity;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransactionCompletedMail extends Mailable
{
  use Queueable, SerializesModels;

  private TransactionEntity $transaction;
  private string $itemName;
  private string $buyerName;

  /**
   * Create a new message instance.
   *
   * @param TransactionEntity $transaction
   * @param string $itemName
   * @param string $buyerName
   */
  public function __construct(TransactionEntity $transaction, string $itemName, string $buyerName)
  {
    $this->transaction = $transaction;
    $this->itemName = $itemName;
    $this->buyerName = $buyerName;
  }

  /**
   * Build the message.
   *
   * @return $this
   */
  public function build()
  {
    return $this->subject('取引が完了しました')
      ->view('emails.transaction_completed')
      ->with([
        'transaction' => $this->transaction,
        'itemName' => $this->itemName,
        'buyerName' => $this->buyerName,
      ]);
  }
}
