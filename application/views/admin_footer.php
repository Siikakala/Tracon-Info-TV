		<div id="footer">
			<p>Copyright &copy; Tracon ry 2010-<?php print date("Y") ?></p>
			<div id="profiler"></div>
		</div>
		<div id="chat">
    		<button id="chathide" onclick="$('#inner_chat').toggle('blind',200);">Näytä/piilota chat</button>
            <div id="inner_chat">
                <div id="chatlog"></div>
                <input type="text" id="chatbox" placeholder="Viestisi.." />
                <input type="text" id="nickbox" size="7" placeholder="Nick" />
            </div>
        </div>
		<div id="dialogs"><?php print $dialogs ?></div>
	</div>
</body>
</html>
