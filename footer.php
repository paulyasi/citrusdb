<table cellpadding=0 cellspacing=0 border=0 width=720 height=180>
<td>
        <table cellpadding=5 cellspacing=0 border=0>
	<?php
    print "<td bgcolor=\"#eeeedd\" width=100 align=center><b class=\"smalltabs\"><a href=\"index.php?load=customer_history&type=fs&account_number=$account_number\" target=\"historyframe\">$l_notes</a></b></a></td>";
	echo '<td>&nbsp;</td>';
    print "<td bgcolor=\"#ddeeee\" width=100 align=center><b class=\"smalltabs\"><a href=\"index.php?load=billing_history&type=fs&account_number=$account_number\" target=\"historyframe\">$l_billing</a></b></td>";
	echo '<td>&nbsp;</td>';
	print "<td bgcolor=\"#eedddd\" width=100 align=center><b class=\"smalltabs\"><a href=\"index.php?load=payment_history&type=fs&account_number=$account_number\" target=\"historyframe\">$l_payments</a></b></td>";
	echo '<td>&nbsp;</td>';
	print "<td bgcolor=\"#dddddd\" width=100 align=center><b class=\"smalltabs\"><a href=\"index.php?load=billing_details&type=fs&account_number=$account_number\" target=\"historyframe\">$l_billing $l_details</a></b></td>";	
	?>
        </table>
</td><tr>
<td width="720" height="160" bgcolor="#eeeedd" valign=top>
	<table border=0 cellpadding=0 cellspacing=0><td>
	<?php
	print "<iframe name=\"historyframe\" src=\"index.php?load=customer_history&type=fs&account_number=$account_number\" width=720 height=160 frameborder=0 marginwidth=0 marginheight=1 scrolling=yes></iframe>";
	?>
	</td></table>
</td>
</table>

