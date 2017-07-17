/**
* Core javascript balloon tool tip pop-out functions within ILance.
*
* @package   	iLance_Functions_Javacript_ToolTip
* @subpackage  	iLance_Functions_Javacript_ToolTip_FollowScroll
* @version	$Revision: 1.0.0 $
* @author       Walter Zorn
*/

//===================	GLOBAL TOOPTIP CONFIGURATION	======================//
config. FollowScroll = false		// true or false - set to true if you want this to be the default behaviour
//=======	END OF TOOLTIP CONFIG, DO NOT CHANGE ANYTHING BELOW	==============//


// Create a new tt_Extension object (make sure that the name of that object,
// here fscrl, is unique amongst the extensions available for
// wz_tooltips.js):
var fscrl = new tt_Extension();

// Implement extension eventhandlers on which our extension should react
fscrl.OnShow = function()
{
	if(tt_aV[FOLLOWSCROLL])
	{
		// Permit FOLLOWSCROLL only if the tooltip is sticky
		if(tt_aV[STICKY])
		{
			var x = tt_x - tt_GetScrollX(), y = tt_y - tt_GetScrollY();

			if(tt_ie)
			{
				fscrl.MoveOnScrl.offX = x;
				fscrl.MoveOnScrl.offY = y;
				fscrl.AddRemEvtFncs(tt_AddEvtFnc);
			}
			else
			{
				tt_SetTipPos(x, y);
				tt_aElt[0].style.position = "fixed";
			}
			return true;
		}
		tt_aV[FOLLOWSCROLL] = false;
	}
	return false;
};
fscrl.OnHide = function()
{
	if(tt_aV[FOLLOWSCROLL])
	{
		if(tt_ie)
			fscrl.AddRemEvtFncs(tt_RemEvtFnc);
		else
			tt_aElt[0].style.position = "absolute";
	}
};
// Helper functions (encapsulate in the class to avoid conflicts with other
// extensions)
fscrl.MoveOnScrl = function()
{
	tt_SetTipPos(fscrl.MoveOnScrl.offX + tt_GetScrollX(), fscrl.MoveOnScrl.offY + tt_GetScrollY());
};
fscrl.AddRemEvtFncs = function(PAddRem)
{
	PAddRem(window, "resize", fscrl.MoveOnScrl);
	PAddRem(window, "scroll", fscrl.MoveOnScrl);
};

