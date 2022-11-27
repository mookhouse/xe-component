/*
countdown_timer.js
*/
function getTimeRemaining(endtime) 
{
	var t = Date.parse(endtime) - Date.parse(new Date());

	var seconds = 0;
	var minutes = 0;
	var hours = 0;
	var days = 0;

	if( t > 0 )
	{
		var seconds = Math.floor((t / 1000) % 60);
		var minutes = Math.floor((t / 1000 / 60) % 60);
		var hours = Math.floor((t / (1000 * 60 * 60)) % 24);
		var days = Math.floor(t / (1000 * 60 * 60 * 24));
		
	}
	else
		t = 0;

	return {
		'total': t,
		'days': days,
		'hours': hours,
		'minutes': minutes,
		'seconds': seconds
	};	
}

function initializeClock(id, endtime) 
{
	var clock = document.getElementById(id);
	var daysSpan = clock.querySelector('.days');
	var hoursSpan = clock.querySelector('.hours');
	var minutesSpan = clock.querySelector('.minutes');
	var secondsSpan = clock.querySelector('.seconds');

	function updateClock() 
	{
		var t = getTimeRemaining(endtime);
		if( t.total > 0 )
		{
			daysSpan.innerHTML = t.days;
			hoursSpan.innerHTML = ('0' + t.hours).slice(-2);
			minutesSpan.innerHTML = ('0' + t.minutes).slice(-2);
			secondsSpan.innerHTML = ('0' + t.seconds).slice(-2);
		}
		else
		{
			clock.innerHTML = '�̺�Ʈ�� ����Ǿ����ϴ�.';
			clearInterval(timeinterval);
		}
	}
	updateClock();
	var timeinterval = setInterval(updateClock, 1000);
}

function initializeCounter(id, nTotalQty)
{
	var $oQty = jQuery(id);
	var $oQty1 = jQuery('#qty', $oQty);
	
	if( nTotalQty > 0 )
		$oQty1.html(_formatNumber(nTotalQty));
	else
		$oQty.hide();	
}
// FormatNumber.js v2  020215
// more info at: jasmint@netsgo.com
function _formatNumber( num )
{ //onkeyup
	temp = new String( num )

	if( temp.length < 1 ) 
		return ''
	// ����ó��
	if( temp.substr( 0, 1 ) == '-' )
		minus = '-'
	else
		minus = ''
	// �Ҽ�������ó��
	dpoint = temp.search( /\./ )

	if( dpoint > 0 ) 
	{	// ù��° ������ .�� �������� �ڸ��� ���������� ���� ����
		dpointVa = '.' + temp.substr( dpoint ).replace( /\D/g, '' );
		temp = temp.substr( 0, dpoint );
	}
	else
		dpointVa = '';

	// �����ܹ̿��� ����
	temp = temp.replace( /\D/g, '' );

	if( temp.length < 4 )
		return minus + temp + dpointVa;
	
	buf = '';

	while( true )
	{
		if( temp.length < 3 ) 
		{
			buf = temp + buf;
			break;
		}

		buf = ',' + temp.substr( temp.length - 3 ) + buf;
		temp = temp.substr( 0, temp.length - 3 );
	}
	if( buf.substr( 0, 1 ) == ',' )
		buf = buf.substr(1);

	return minus + buf + dpointVa;
}