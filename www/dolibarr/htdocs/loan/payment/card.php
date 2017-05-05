<?php
/* Copyright (C) 2014		Alexandre Spangaro	<aspangaro.dolibarr@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	    \file       htdocs/loan/payment/card.php
 *		\ingroup    loan
 *		\brief      Payment's card of loan
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/loan/class/loan.class.php';
require_once DOL_DOCUMENT_ROOT.'/loan/class/paymentloan.class.php';
if (! empty($conf->banque->enabled)) require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';

$langs->load('bills');
$langs->load('banks');
$langs->load('companies');
$langs->load('loan');

// Security check
$id=GETPOST("id");
$action=GETPOST("action");
$confirm=GETPOST('confirm');
if ($user->societe_id) $socid=$user->societe_id;
// TODO ajouter regle pour restreindre acces paiement
//$result = restrictedArea($user, 'facture', $id,'');

$payment = new PaymentLoan($db);
if ($id > 0) 
{
	$result=$payment->fetch($id);
	if (! $result) dol_print_error($db,'Failed to get payment id '.$id);
}


/*
 * Actions
 */

// Delete payment
if ($action == 'confirm_delete' && $confirm == 'yes' && $user->rights->loan->delete)
{
	$db->begin();

	$result = $payment->delete($user);
	if ($result > 0)
	{
        $db->commit();
        header("Location: ".DOL_URL_ROOT."/loan/index.php");
        exit;
	}
	else
	{
		setEventMessages($payment->error, $payment->errors, 'errors');
        $db->rollback();
	}
}

// Create payment
if ($action == 'confirm_valide' && $confirm == 'yes' && $user->rights->loan->write)
{
	$db->begin();

	$result=$payment->valide();
	
	if ($result > 0)
	{
		$db->commit();

		$factures=array();	// TODO Get all id of invoices linked to this payment
		foreach($factures as $id)
		{
			$fac = new Facture($db);
			$fac->fetch($id);

			$outputlangs = $langs;
			if (! empty($_REQUEST['lang_id']))
			{
				$outputlangs = new Translate("",$conf);
				$outputlangs->setDefaultLang($_REQUEST['lang_id']);
			}
			if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE)) {
				$fac->generateDocument($fac->modelpdf, $outputlangs);
			}
		}

		header('Location: card.php?id='.$payment->id);
		exit;
	}
	else
	{
		setEventMessages($payment->error, $payment->errors, 'errors');
		$db->rollback();
	}
}


/*
 * View
 */

llxHeader();

$loan = new Loan($db);
$form = new Form($db);

$h=0;

$head[$h][0] = DOL_URL_ROOT.'/loan/payment/card.php?id='.$_GET["id"];
$head[$h][1] = $langs->trans("Card");
$hselected = $h;
$h++;

dol_fiche_head($head, $hselected, $langs->trans("PaymentLoan"), 0, 'payment');

/*
 * Confirm deletion of the payment
 */
if ($action == 'delete')
{
	print $form->formconfirm('card.php?id='.$payment->id, $langs->trans("DeletePayment"), $langs->trans("ConfirmDeletePayment"), 'confirm_delete','',0,2);
}

/*
 * Confirm validation of the payment
 */
if ($action == 'valide')
{
	$facid = $_GET['facid'];
	print $form->formconfirm('card.php?id='.$payment->id.'&amp;facid='.$facid, $langs->trans("ValidatePayment"), $langs->trans("ConfirmValidatePayment"), 'confirm_valide','',0,2);	
}


print '<table class="border" width="100%">';

// Ref
print '<tr><td valign="top" width="140">'.$langs->trans('Ref').'</td>';
print '<td colspan="3">';
print $form->showrefnav($payment,'id','',1,'rowid','id');
print '</td></tr>';

// Date
print '<tr><td valign="top" width="120">'.$langs->trans('Date').'</td><td colspan="3">'.dol_print_date($payment->datep,'day').'</td></tr>';

// Mode
print '<tr><td valign="top">'.$langs->trans('Mode').'</td><td colspan="3">'.$langs->trans("PaymentType".$payment->type_code).'</td></tr>';

// Number
print '<tr><td valign="top">'.$langs->trans('Number').'</td><td colspan="3">'.$payment->num_payment.'</td></tr>';

// Amount
print '<tr><td valign="top">'.$langs->trans('LoanCapital').'</td><td colspan="3">'.price($payment->amount_capital, 0, $outputlangs, 1, -1, -1, $conf->currency).'</td></tr>';
print '<tr><td valign="top">'.$langs->trans('Insurance').'</td><td colspan="3">'.price($payment->amount_insurance, 0, $outputlangs, 1, -1, -1, $conf->currency).'</td></tr>';
print '<tr><td valign="top">'.$langs->trans('Interest').'</td><td colspan="3">'.price($payment->amount_interest, 0, $outputlangs, 1, -1, -1, $conf->currency).'</td></tr>';

// Note Private
print '<tr><td valign="top">'.$langs->trans('NotePrivate').'</td><td colspan="3">'.nl2br($payment->note_private).'</td></tr>';

// Note Public
print '<tr><td valign="top">'.$langs->trans('NotePublic').'</td><td colspan="3">'.nl2br($payment->note_public).'</td></tr>';

// Bank account
if (! empty($conf->banque->enabled))
{
    if ($payment->bank_account)
    {
    	$bankline=new AccountLine($db);
    	$bankline->fetch($payment->bank_line);

    	print '<tr>';
    	print '<td>'.$langs->trans('BankTransactionLine').'</td>';
		print '<td colspan="3">';
		print $bankline->getNomUrl(1,0,'showall');
    	print '</td>';
    	print '</tr>';
    }
}

print '</table>';


/*
 * List of loans payed
 */

$disable_delete = 0;
$sql = 'SELECT l.rowid as id, l.label, l.paid, l.capital as capital, pl.amount_capital, pl.amount_insurance, pl.amount_interest';
$sql.= ' FROM '.MAIN_DB_PREFIX.'payment_loan as pl,'.MAIN_DB_PREFIX.'loan as l';
$sql.= ' WHERE pl.fk_loan = l.rowid';
$sql.= ' AND l.entity = '.$conf->entity;
$sql.= ' AND pl.rowid = '.$payment->id;

dol_syslog("loan/payment/card.php", LOG_DEBUG);
$resql=$db->query($sql);
if ($resql)
{
	$num = $db->num_rows($resql);

	$i = 0;
	$total = 0;
	print '<br><table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td>'.$langs->trans('Loan').'</td>';
	print '<td>'.$langs->trans('Label').'</td>';
	print '<td align="right">'.$langs->trans('ExpectedToPay').'</td>';
	print '<td align="center">'.$langs->trans('Status').'</td>';
	print '<td align="right">'.$langs->trans('PayedByThisPayment').'</td>';
	print "</tr>\n";

	if ($num > 0)
	{
		$var=True;

		while ($i < $num)
		{
			$objp = $db->fetch_object($resql);

			$var=!$var;
			print '<tr '.$bc[$var].'>';
			// Ref
			print '<td>';
			$loan->fetch($objp->id);
			print $loan->getLinkUrl(1);
			print "</td>\n";
			// Label
			print '<td>'.$objp->label.'</td>';
			// Expected to pay
			print '<td align="right">'.price($objp->capital).'</td>';
			// Status
			print '<td align="center">'.$loan->getLibStatut(4,$objp->amount_capital).'</td>';
			// Amount payed
			print '<td align="right">'.price($objp->amount_capital).'</td>';
			print "</tr>\n";
			if ($objp->paid == 1)	// If at least one invoice is paid, disable delete
			{
				$disable_delete = 1;
			}
			$total = $total + $objp->amount_capital;
			$i++;
		}
	}
	$var=!$var;

	print "</table>\n";
	$db->free($resql);
}
else
{
	dol_print_error($db);
}

print '</div>';


/*
 * Actions buttons
 */
print '<div class="tabsAction">';

/*
if (! empty($conf->global->BILL_ADD_PAYMENT_VALIDATION))
{
	if ($user->societe_id == 0 && $payment->statut == 0 && $_GET['action'] == '')
	{
		if ($user->rights->facture->paiement)
		{
			print '<a class="butAction" href="card.php?id='.$_GET['id'].'&amp;facid='.$objp->facid.'&amp;action=valide">'.$langs->trans('Valid').'</a>';
		}
	}
}
*/

if (empty($action) && ! empty($user->rights->loan->delete))
{
	if (! $disable_delete)
	{
		print '<a class="butActionDelete" href="card.php?id='.$_GET['id'].'&amp;action=delete">'.$langs->trans('Delete').'</a>';
	}
	else
	{
		print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("CantRemovePaymentWithOneInvoicePaid")).'">'.$langs->trans('Delete').'</a>';
	}
}

print '</div>';



llxFooter();

$db->close();
