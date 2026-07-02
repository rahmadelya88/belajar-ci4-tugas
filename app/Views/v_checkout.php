<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="row">
    <div class="col-lg-6">
        <?= form_open('buy', 'class="row g-3"') ?>

        <?= form_hidden('username', session()->get('username')) ?>
        <input type="hidden" name="total_harga" id="total_harga" value="">
        
        <div class="col-12">
            <?= form_label('Nama', 'nama', ['class' => 'form-label']) ?>
            <?= form_input([
                'name'     => 'nama',
                'id'       => 'nama',
                'class'    => 'form-control',
                'value'    => session()->get('username'),
                'readonly' => true]) ?>
        </div>

        <div class="col-12">
            <?= form_label('Alamat', 'alamat', ['class' => 'form-label']) ?>
            <?= form_input([
                'name'  => 'alamat',
                'id'    => 'alamat',
                'class' => 'form-control']) ?>
        </div> 

        <div class="col-12"> 
            <?= form_label('Kelurahan', 'kelurahan', ['class' => 'form-label']) ?>
            <?= form_dropdown('kelurahan', [], '', ['id' => 'kelurahan', 'class' => 'form-control']) ?>
        </div>

        <div class="col-12"> 
            <?= form_label('Layanan', 'layanan', ['class' => 'form-label']) ?> 
            <?= form_dropdown('layanan', [], '', ['id' => 'layanan', 'class' => 'form-control']) ?>        </div>
        </div>

        <div class="col-12">
            <?= form_label('Kode Kupon', 'kupon_code', ['class' => 'form-label']) ?>
            <?= form_input([
                'name'        => 'kupon_code',
                'id'          => 'kupon_code',
                'class'       => 'form-control',
                'placeholder' => 'Contoh: HEMAT20'
            ]) ?>
        </div>

        <div class="col-12">
            <?= form_label('Ongkir', 'ongkir', ['class' => 'form-label']) ?>
            <?= form_input([
                'name'     => 'ongkir',
                'id'       => 'ongkir',
                'class'    => 'form-control',
                'readonly' => true]) ?>
        </div>

        <div class="col-12">
            <?= form_submit(
                'submit',
                'Buat Pesanan',
                ['class' => 'btn btn-primary']) ?>
        </div>

        <?= form_close() ?> 
    </div>
    <div class="col-lg-6">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Nama</th>
                    <th scope="col">Harga</th>
                    <th scope="col">Jumlah</th>
                    <th scope="col">Sub Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (!empty($items)) :
                    foreach ($items as $index => $item) :
                ?>
                    <tr>
                        <td><?= $item['name'] ?></td>
                        <td><?= number_to_currency($item['price'], 'IDR') ?></td>
                        <td><?= $item['qty'] ?></td>
                        <td><?= number_to_currency($item['price'] * $item['qty'], 'IDR') ?></td>
                    </tr>
                <?php
                    endforeach;
                endif;
                ?>

                <tr>
                    <td colspan="2"></td>
                    <td>Subtotal</td>
                    <td><?= number_to_currency($total, 'IDR') ?></td>
                </tr>

                <tr>
                    <td colspan="2"></td>
                    <td>Diskon Kupon</td>
                    <td><span id="diskon">IDR 0</span></td>
                </tr>

                <tr>
                    <td colspan="2"></td>
                    <td>PPN (12%)</td>
                    <td><span id="ppn">IDR 0</span></td>
                </tr>

                <tr>
                    <td colspan="2"></td>
                    <td>Biaya Admin</td>
                    <td><span id="admin">IDR 0</span></td>
                </tr>

                <tr>
                    <td colspan="2"></td>
                    <td>Ongkir</td>
                    <td><span id="ongkir_view">IDR 0</span></td>
                </tr>

                <tr class="table-primary">
                    <td colspan="2"></td>
                    <td><strong>Grand Total</strong></td>
                    <td><strong><span id="total"><?= number_to_currency($total, 'IDR') ?></span></strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('script') ?>
<script>
$(document).ready(function() {
    let ongkir = 0;
    let subtotal = <?= $total ?>;
    hitungTotal();

    function hitungTotal() {
        let kupon = $("#kupon_code").val().toUpperCase();
        let diskon = 0;

        if (kupon == "HEMAT20")
            diskon = subtotal * 0.20;

        else if (kupon == "HEMAT30")
            diskon = subtotal * 0.30;

        else if (kupon == "MEMBER25")
            diskon = subtotal * 0.25;

        let ppn = subtotal * 0.12;
        let admin = 0;

        if (subtotal <= 15000000)
            admin = subtotal * 0.005;
        else if (subtotal <= 35000000)
            admin = subtotal * 0.007;
        else
            admin = subtotal * 0.009;

        let grandTotal = subtotal - diskon + ppn + admin + ongkir;

        $("#diskon").text("IDR " + diskon.toLocaleString("id-ID"));
        $("#ppn").text("IDR " + ppn.toLocaleString("id-ID"));
        $("#admin").text("IDR " + admin.toLocaleString("id-ID"));
        $("#ongkir_view").text("IDR " + ongkir.toLocaleString("id-ID"));
        $("#ongkir").val(ongkir);
        $("#total").text("IDR " + grandTotal.toLocaleString("id-ID"));
        $("#total_harga").val(grandTotal);
    }

	$('#kelurahan').select2({
	    placeholder: 'Cari daerah tujuan',
	    minimumInputLength: 3,
        ajax: {
            url: '<?= site_url('ajax/destinations') ?>',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    q: params.term
                };
            },
            processResults: function(data) {
                return data;
            },
            cache: true
        } 
	});

    $("#kelurahan").on('change', function () {
        let id_kelurahan = $(this).val();

        $("#layanan").empty();
        ongkir = 0;
        hitungTotal(); 

        $.ajax({
            url: "<?= site_url('ajax/costs') ?>", 
            dataType: "json",
            data: {
                destination: id_kelurahan
            },
            success: function (data) { 
                data.forEach(function (item) {
                    $("#layanan").append(
                        $('<option>', {
                            value: item.cost,
                            text: `${item.description} (${item.service}) : estimasi ${item.etd}`
                        })
                    );
                });
            }
        });
    });

    $("#layanan").on('change', function() {
    ongkir = parseInt($(this).val());
    hitungTotal();
    }); 

    $("#kupon_code").on("keyup change", function () {
    hitungTotal();
    });
});
</script>
<?= $this->endSection() ?>