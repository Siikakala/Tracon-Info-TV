<div id="dialog-tekstari-help" title="Virhekoodit">
	Virhekoodit (tekstit suoraa Nexmon sivuilta):
	<table class="stats">
		<th class="ui-state-default">1xx</th><th class="ui-state-default">Lähetysvirheet</th><th class="ui-state-default">&nbsp;</th>
		<tr><td>0</td><td>Success</td><td>The message was successfully accepted for delivery by Nexmo</td></tr>
		<tr><td>1</td><td>Throttled</td><td>You have exceeded the submission capacity allowed on this account, please back-off and retry</td></tr>
		<tr><td>2</td><td>Missing params</td><td>Your request is incomplete and missing some mandatory parameters</td></tr>
		<tr><td>3</td><td>Invalid params</td><td>The value of one or more parameters is invalid</td></tr>
		<tr><td>4</td><td>Invalid credentials</td><td>The api_key / api_secret you supplied is either invalid or disabled</td></tr>
		<tr><td>5</td><td>Internal error</td><td>An error has occurred in the Nexmo platform whilst processing this message</td></tr>
		<tr><td>6</td><td>Invalid message</td><td>The Nexmo platform was unable to process this message, for example, an un-recognized number prefix</td></tr>
		<tr><td>7</td><td>Number barred</td><td>The number you are trying to submit to is blacklisted and may not receive messages</td></tr>
		<tr><td>8</td><td>Partner account barred</td><td>The api_key you supplied is for an account that has been barred from submitting messages</td></tr>
		<tr><td>9</td><td>Partner quota exceeded</td><td>Your pre-pay account does not have sufficient credit to process this message</td></tr>
		<tr><td>10</td><td>Too many existing binds</td><td>The number of simultaneous connections to the platform exceeds the capabilities of your account</td></tr>
		<tr><td>11</td><td>Account not enabled for REST</td><td>This account is not provisioned for REST submission, you should use SMPP instead</td></tr>
		<tr><td>12</td><td>Message too long</td><td>Applies to Binary submissions, where the length of the UDH and the message body combined exceed 140 octets</td></tr>
		<tr><td>13</td><td>Communication Failed</td><td>Message was not submitted because there was a communication failure</td></tr>
		<tr><td>14</td><td>Invalid Signature</td><td>Message was not submitted due to a verification failure in the submitted signature</td></tr>
		<tr><td>15</td><td>Invalid sender address</td><td>The sender address (from parameter) was not allowed for this message. Restrictions may apply depending on the destination see our FAQs</td></tr>
		<tr><td>16</td><td>Invalid TTL</td><td>The ttl parameter values is invalid</td></tr>
		<tr><td>19</td><td>Facility not allowed</td><td>Your request makes use of a facility that is not enabled on your account</td></tr>
		<tr><td>20</td><td>Invalid Message class</td><td>The message class value supplied was out of range (0 - 3)</td></tr>
	</table>
	<table class="stats">
		<th class="ui-state-default">2xx & 3xx</th><th class="ui-state-default">Välitysvirheet</th>
		<tr><td>0</td><td>Delivered</td></tr>
		<tr><td>1</td><td>Unknown</td></tr>
		<tr><td>2</td><td>Absent Subscriber - Temporary</td></tr>
		<tr><td>3</td><td>Absent Subscriber - Permanent</td></tr>
		<tr><td>4</td><td>Call barred by user</td></tr>
		<tr><td>5</td><td>Portability Error</td></tr>
		<tr><td>6</td><td>Anti-Spam Rejection</td></tr>
		<tr><td>7</td><td>Handset Busy</td></tr>
		<tr><td>8</td><td>Network Error</td></tr>
		<tr><td>9</td><td>Illegal Number</td></tr>
		<tr><td>10</td><td>Invalid Message</td></tr>
		<tr><td>11</td><td>Unroutable</td></tr>
		<tr><td>99</td><td>General Error</td></tr>
	</table>
</div>