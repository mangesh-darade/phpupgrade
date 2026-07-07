<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div class="modal-dialog modal-lg no-modal-header">
    <div class="modal-content">
        <div class="modal-body">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                <i class="fa fa-2x">&times;</i>
            </button>
            <button type="button" class="btn btn-xs btn-default no-print pull-right" style="margin-right:15px;" onclick="window.print();">
                <i class="fa fa-print"></i> <?= lang('print'); ?>
            </button>
            <?php if ($logo) { ?>
                <div class="text-center" style="margin-bottom:20px;">
                    <img src="<?= base_url() . 'assets/mdata/' . $Customer_assets . '/uploads/logos/' . $Settings->logo; ?>"
                         alt="<?= $Settings->site_name; ?>">
                </div>
            <?php } ?>
            <div class="well well-sm">
                <div class="row bold">
                    <div class="col-xs-7">
                        <p class="bold">
                            <?= lang("date"); ?>: <?= $this->sma->hrld($inv->date); ?><br>
                            <?= lang("return_purchase_no"); ?>: <?= $inv->return_purchase_ref ? $inv->return_purchase_ref : $inv->reference_no; ?><br>
                            <?php if (!empty($purchase)) { ?>
                                <?= lang("purchase_no"); ?>: <?= $purchase->reference_no; ?>
                                <a href="<?= site_url('purchases/view/' . $purchase->id); ?>" class="no-print"><i class="fa fa-external-link"></i></a><br>
                            <?php } ?>
                            <?= lang("status"); ?>: <?= lang($inv->status); ?>
                        </p>
                    </div>
                    <div class="col-xs-5 text-right order_barcodes">
                        <?= $barcode; ?>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>

            <div class="row" style="margin-bottom:15px;">
                <div class="col-xs-6">
                    <strong><?= lang("from"); ?></strong>
                    <h2 style="margin-top:10px;"><?= $Settings->site_name; ?></h2>
                    <?php if (!empty($warehouse)) { ?>
                        <address><b><?= lang('warehouse'); ?>:</b> <?= $warehouse->name; ?></address>
                    <?php } ?>
                </div>
                <div class="col-xs-6">
                    <strong><?= lang("Supplier Details"); ?></strong>
                    <?php if (!empty($supplier)) { ?>
                        <h2 style="margin-top:10px;"><?= $supplier->company ? $supplier->company : $supplier->name; ?></h2>
                        <address>
                            <?= ($supplier->phone != '') ? '<b>' . lang("tel") . ':</b> ' . $supplier->phone : ''; ?>
                            <?= ($supplier->email != '') ? '<br/><b>' . lang("email") . ':</b> ' . $supplier->email : ''; ?>
                        </address>
                    <?php } ?>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped print-table order-table">
                    <thead>
                    <tr>
                        <th><?= lang("no"); ?></th>
                        <th><?= lang("description"); ?></th>
                        <?php if ($Owner || $Admin || ($GP['products-cost'] == '1')) { ?>
                            <th><?= lang("unit_cost"); ?></th>
                        <?php } ?>
                        <th><?= lang("quantity"); ?></th>
                        <th><?= lang("subtotal"); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $r = 1;
                    if (!empty($rows)) {
                        foreach ($rows as $row) {
                            ?>
                            <tr>
                                <td style="text-align:center; width:40px;"><?= $r; ?></td>
                                <td>
                                    <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                </td>
                                <?php if ($Owner || $Admin || ($GP['products-cost'] == '1')) { ?>
                                    <td style="text-align:right;"><?= $this->sma->formatMoney($row->real_unit_cost); ?></td>
                                <?php } ?>
                                <td style="text-align:center;"><?= $this->sma->formatQuantity($row->unit_quantity); ?></td>
                                <td style="text-align:right;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                            </tr>
                            <?php
                            $r++;
                        }
                    }
                    ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="<?= ($Owner || $Admin || ($GP['products-cost'] == '1')) ? 4 : 3; ?>"
                            style="text-align:right; font-weight:bold;"><?= lang("return_amount"); ?></td>
                        <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney($inv->grand_total); ?></td>
                    </tr>
                    <?php if (!empty($inv->surcharge)) { ?>
                        <tr>
                            <td colspan="<?= ($Owner || $Admin || ($GP['products-cost'] == '1')) ? 4 : 3; ?>"
                                style="text-align:right;"><?= lang("return_surcharge"); ?></td>
                            <td style="text-align:right;"><?= $this->sma->formatMoney($inv->surcharge); ?></td>
                        </tr>
                    <?php } ?>
                    </tfoot>
                </table>
            </div>

            <?php if (!empty($inv->note)) { ?>
                <div class="well well-sm">
                    <p class="bold"><?= lang("note"); ?>:</p>
                    <div><?= $this->sma->decode_html($inv->note); ?></div>
                </div>
            <?php } ?>

            <?php if (!empty($user)) { ?>
                <div class="well well-sm">
                    <p><?= lang("created_by"); ?>: <?= $user->first_name . ' ' . $user->last_name; ?></p>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
