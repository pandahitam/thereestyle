<br>
<fieldset>
	<legend>Payment Email</legend>
	<p>Jika pesanan customer sudah fix, silakan tekan tombol di bawah untuk mengirim email informasi pembayaran ke customer.</p>
	<form method="POST" action="{$this_path}send.php">
		<input type="hidden" name="id_order" value="{$id_order}" />
		<input type="submit" value="Send Payment Email" class="button" />
	</form>
</fieldset>