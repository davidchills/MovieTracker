/*jslint devel: true, browser: true, white: true, continue: true, plusplus: false, regexp: true, forin: true*/
/*global jQuery: true, validationObj: true, dhf: true, scrollTo: true, tdFillingArray: true, console: true, Logger: true*/


/*  Disabled conflicts with jQuery  */

if (window.jQuery) { jQuery.noConflict(); }

function $D(id) {
    'use strict';
    return document.getElementById(id);
}

var dhf = {
    defaultInputText: { 'maxSize': '4000', 'oAutocomplete': 'no' },
    defaultInputNumber: { 'oAutocomplete': 'off', 'oMin': 0, 'oMax': 0, 'oStep': 1 },
    defaultInputSearch: { 'oAutocomplete': 'off' },
    defaultInputTel: { 'oPlaceholder': '123.456.789', 'oPattern': '^\\d{3}[\\.|-]\\d{3}[\\.|-]\\d{4}$', 'oAutocomplete': 'off', 'maxSize' : 12 },
    defaultInputUrl: { 'oAutocomplete': 'no' },
    defaultInputEmail: { 'oPlaceholder': 'attuid@att.com', 'oAutocomplete': 'off'  },
    defaultInputPassword: { 'maxSize': '4000', 'oAutocomplete': 'off'  },
    defaultInputTextArea: { 'maxSize': '4000', 'oAutocomplete': 'off' },
    defaultInputButton: { 'ocn': 'thin' },
    defaultInputSubmit: { 'ocn': 'thin' },
    defaultInputCheckbox: {},
    defaultInputRadio: {},
    defaultButtonButton: { 'ocn': 'thin' },
    defaultInputSelect: { 'oSize': '1' },
    defaultTable: { 'oPad': '0', 'oSpace': '0', 'oBorder': '0' },
    defaultTableRow: {},
    defaultTableCell: { 'oAlign': 'left', 'vAlign': 'middle' },
    defaultDiv: {},
    defaultIframe: { 'oid': 'warningPopUpIframe', 'parentId': 'documentBody', 'oScroll': 'auto' },
    defaultSpan: {},
    defaultParagraph: {},
    defaultImage: { 'oBorder': '0' },
    defaultInputLabel: {},
    defaultAnchor: {},
    defaultMailto: { 'to': 'dh806j@att.com', 'iHTML': 'Send Email' },
    javascriptLogging: { 'level': 'none' },
    rowColor: { 'increment': 1 },
    costSavingsFactor: 5.08,

    /**
     *  Loops through a passed in (single dimensional) array
     *  Creates a temp array with any duplicates and empty values removed and passes that back
     */
    arrayUnique: function (oArray) {
        'use strict';
        var nArray = [],
            oLength = oArray.length,
            oValue,
            i = 0;

        for (i = 0; i < oLength; i += 1) {
            if ((oArray[i]) && typeof oArray[i] === 'string') {
                oValue = dhf.trim(oArray[i]);
                if (oValue !== '' && oValue !== undefined && jQuery.inArray(oValue, nArray) === -1) { nArray.push(oValue); }
            }
            else { nArray = oArray; }
        }
        return nArray;
    },


    /**
     * Setter method to set the default value for a parameter
     * @param string defaultType - the name of the element type
     * @param string dParam - the name of the parameter
     * @return void
     */
    setDefaults: function (defaultType, dParams) {
        'use strict';
        var attribute,
            tmpObj;

        if (!dhf[defaultType]) { return; }
        tmpObj = dhf[defaultType];
        for (attribute in dParams) {
            if ((dParams[attribute] === '') && tmpObj[attribute] !== undefined) { delete tmpObj[attribute]; }
            else { tmpObj[attribute] = dParams[attribute]; }
        }
    },

    /**
     * Getter method to get the default value for a parameter
     * @param string defaultType - the name of the element type
     * @param string dParam - the name of the parameter
     * @return string
     */
    getDefault: function (defaultType, dParam) {
        'use strict';
        var tmpObj,
            returnValue = '';

        if (!dhf[defaultType]) { returnValue = ''; }
        tmpObj = dhf[defaultType];
        if (tmpObj[dParam] !== undefined) { returnValue = tmpObj[dParam]; }
        return returnValue;
    },

    /**
     * Creates inner DOM objects to an existing object, such as table cells in a row
     * @param object domObj - The parent object that the children will be created under
     * @param array iObjs - An array of JSON objects with details about what elements to create
     * @return void
     */
    makeSubObjects: function (domObj, iObjs) {
        'use strict';
        var i = 0;

        if (typeof iObjs === 'object') { for (i = 0; i < iObjs.length; i += 1) { domObj.appendChild(dhf[iObjs[i].iFunctName](iObjs[i].iParams)); } }
    },


    addParams: function (tag, oParams, type) {
        'use strict';
        var vReadOnly,
            vIsDisabled,
            disableJS,
            domObj,
            i;

        domObj = document.createElement(tag);
        if (type) { domObj.setAttribute('type', type); }
        if (oParams.isDisabled === 'Y') { domObj.setAttribute('disabled', 'disabled'); }
        if (oParams.readOnly === 'Y') { domObj.setAttribute('readonly', 'readonly'); }
        if (oParams.isDisabled === 'Y' || oParams.readOnly === 'Y') {
        	if (oParams.oPattern) { delete oParams.oPattern; }
        	if (oParams.oMin) { delete oParams.oMin; }
        	if (oParams.oMax) { delete oParams.oMax; }
        	if (oParams.oStep) { delete oParams.oStep; }
        	if (oParams.oRequired) { delete oParams.oRequired; }
        	if (oParams.oAutofocus) { delete oParams.oAutofocus; }
        	if (oParams.oClick) { delete oParams.oClick; }
        	if (oParams.oFocus) { delete oParams.oFocus; }
        	if (oParams.oBlur) { delete oParams.oBlur; }
        	if (oParams.oChange) { delete oParams.oChange; }
        	if (oParams.okeyUp) { delete oParams.okeyUp; }
        	if (oParams.oMouseOver) { delete oParams.oMouseOver; }
        	if (oParams.oMouseOut) { delete oParams.oMouseOut; }
        	if (oParams.oMouseDown) { delete oParams.oMouseDown; }
        }
        if (oParams.oid) { domObj.setAttribute('id', oParams.oid); }
        if (oParams.oName) { domObj.setAttribute('name', oParams.oName); }
        if (oParams.oTitle) { domObj.setAttribute('title', oParams.oTitle); }
        if (oParams.oStyle) { domObj.setAttribute('style', oParams.oStyle); }
        if (oParams.ocn) { domObj.className = oParams.ocn; }
        if (oParams.oValue) { domObj.setAttribute('value', dhf.trim(oParams.oValue)); }
        if (oParams.ov) { domObj.setAttribute('value', dhf.trim(oParams.ov)); }
        if (oParams.oAttr && typeof oParams.oAttr === "object") {
            if (!oParams.oAttr.oName) {
                for (i = 0; i < oParams.oAttr.length; i += 1) {
                    domObj.setAttribute(oParams.oAttr[i].oName, oParams.oAttr[i].oValue);
                }
            }
            else { domObj.setAttribute(oParams.oAttr.oName, oParams.oAttr.oValue); }
        }

        // For some html5 form attributes
        if (oParams.oPlaceholder) { domObj.setAttribute('placeholder', oParams.oPlaceholder); }
        if (oParams.oPattern) { domObj.setAttribute('pattern', oParams.oPattern); }
        if (oParams.oMin) { domObj.setAttribute('min', oParams.oMin); }
        if (oParams.oMax) { domObj.setAttribute('max', oParams.oMax); }
        if (oParams.oStep) { domObj.setAttribute('step', oParams.oStep); }
        if (oParams.oRequired) { domObj.setAttribute('required', oParams.oRequired); }
        if (oParams.oAutofocus) { domObj.setAttribute('autofocus', oParams.oAutofocus); }
        if (oParams.oAutocomplete) { domObj.setAttribute('autocomplete', oParams.oAutocomplete); }

        if (oParams.oSize) { domObj.setAttribute('size', oParams.oSize); }

        // Input Text
        if (oParams.maxSize) { domObj.setAttribute('maxlength', oParams.maxSize); }

        // Textarea specific
        if (oParams.oRows) { domObj.setAttribute('rows', oParams.oRows); }
        if (oParams.oCols) { domObj.setAttribute('cols', oParams.oCols); }

        // Label Specific
        if (oParams.oFor) { domObj.setAttribute('for', oParams.oFor); }

        // Checkbox, Radio Button, Select
        if (oParams.os === 'Y') {
        	if (type === 'radio' || type === 'checkbox') { domObj.checked = true; }
            else { domObj.setAttribute('selected','selected'); }
        }

        // Image, URL, iFrame
        if (oParams.oSrc) { domObj.setAttribute('src', oParams.oSrc); }
        if (oParams.oScroll) { domObj.setAttribute('scrolling', oParams.oScroll); }
        if (oParams.oTarget) { domObj.setAttribute('target', oParams.oTarget); }
        if (oParams.oHref) { domObj.setAttribute('href', oParams.oHref); }

        if (oParams.oAlt) { domObj.setAttribute('alt', oParams.oAlt); }
        else if (oParams.oTitle) { domObj.setAttribute('alt', oParams.oTitle); }

        // Table Specific
        if (oParams.oWidth) { domObj.setAttribute('width', oParams.oWidth); }
        if (oParams.oHeight) { domObj.setAttribute('height', oParams.oHeight); }
        if (oParams.oPad) { domObj.setAttribute('cellpadding', oParams.oPad); }
        if (oParams.oSpace) { domObj.setAttribute('cellspacing', oParams.oSpace); }
        if (oParams.oBorder) { domObj.setAttribute('border', oParams.oBorder); }

        if (oParams.oAlign) { domObj.setAttribute('align', oParams.oAlign); }
        if (oParams.vAlign) { domObj.setAttribute('valign', oParams.vAlign); }
        if (oParams.nWrap) { domObj.setAttribute('nowrap', 'nowrap'); }
        if (oParams.cSpan) { domObj.setAttribute('colspan', oParams.cSpan); }
        if (oParams.rSpan) { domObj.setAttribute('rowspan', oParams.rSpan); }

		if (oParams.oClick) { domObj.setAttribute('onclick', dhf.fmmjc(oParams.oClick)); }
		if (oParams.oFocus) { domObj.setAttribute('onfocus', dhf.fmmjc(oParams.oFocus)); }
		if (oParams.oBlur) { domObj.setAttribute('onblur', dhf.fmmjc(oParams.oBlur)); }
		if (oParams.oChange) { domObj.setAttribute('onchange', dhf.fmmjc(oParams.oChange)); }
		if (oParams.okeyUp) { domObj.setAttribute('onkeyup', dhf.fmmjc(oParams.okeyUp)); }
		if (oParams.oMouseOver) { domObj.setAttribute('onmouseover', dhf.fmmjc(oParams.oMouseOver)); }
		if (oParams.oMouseOut) { domObj.setAttribute('onmouseout', dhf.fmmjc(oParams.oMouseOut)); }
		if (oParams.oMouseDown) { domObj.setAttribute('onmousedown', dhf.fmmjc(oParams.oMouseDown)); }

        return domObj;
    },


    addParamsIE: function (oParams) {
        'use strict';
        var vReadOnly,
            vIsDisabled,
            disableJS,
            objString = '',
            i;

        if (oParams.isDisabled === 'Y') { objString += 'disabled="disabled" '; }
        if (oParams.readOnly === 'Y') { objString += 'readonly="readonly" '; }

        if (oParams.isDisabled === 'Y' || oParams.readOnly === 'Y') {
        	if (oParams.oPattern) { delete oParams.oPattern; }
        	if (oParams.oMin) { delete oParams.oMin; }
        	if (oParams.oMax) { delete oParams.oMax; }
        	if (oParams.oStep) { delete oParams.oStep; }
        	if (oParams.oRequired) { delete oParams.oRequired; }
        	if (oParams.oAutofocus) { delete oParams.oAutofocus; }
        	if (oParams.oClick) { delete oParams.oClick; }
        	if (oParams.oFocus) { delete oParams.oFocus; }
        	if (oParams.oBlur) { delete oParams.oBlur; }
        	if (oParams.oChange) { delete oParams.oChange; }
        	if (oParams.okeyUp) { delete oParams.okeyUp; }
        	if (oParams.oMouseOver) { delete oParams.oMouseOver; }
        	if (oParams.oMouseOut) { delete oParams.oMouseOut; }
        	if (oParams.oMouseDown) { delete oParams.oMouseDown; }
        }


        if (oParams.oid) { objString += 'id="' + oParams.oid + '" '; }
        if (oParams.oName) { objString += 'name="' + oParams.oName + '" '; }
        if (oParams.oTitle) { objString += 'title="' + oParams.oTitle + '" '; }

        if (oParams.oAlt) { objString += 'alt="'+oParams.oAlt+'" '; }
        else if (oParams.oTitle) { objString += 'alt="'+oParams.oTitle+'" '; }

        if (oParams.oStyle) { objString += 'style="' + oParams.oStyle + '" '; }
        if (oParams.ocn) { objString += 'class="' + oParams.ocn + '" '; }

        if (oParams.oValue) { objString += 'value="' + dhf.trim(oParams.oValue) + '" '; }
        if (oParams.ov) { objString += 'value="' + dhf.trim(oParams.ov) + '" '; }

        if (oParams.oAttr && typeof oParams.oAttr === "object") {
            if (!oParams.oAttr.oName) {
                for (i = 0; i < oParams.oAttr.length; i += 1) {
                    objString += oParams.oAttr[i].oName+'="' + oParams.oAttr[i].oValue + '" ';
                }
            }
            else { objString += oParams.oAttr.oName+'="' + oParams.oAttr.oValue + '" '; }
        }

        // For some html5 form attributes
        if (oParams.oPlaceholder) { objString += 'placeholder="' + oParams.oPlaceholder + '" '; }
        if (oParams.oPattern) { objString += 'pattern="' + oParams.oPattern + '" '; }
        if (oParams.oMin) { objString += 'min="' + oParams.oMin + '" '; }
        if (oParams.oMax) { objString += 'max="' + oParams.oMax + '" '; }
        if (oParams.oStep) { objString += 'step="' + oParams.oStep + '" '; }
        if (oParams.oRequired) { objString += 'required="' + oParams.oRequired + '" '; }
        if (oParams.oAutofocus) { objString += 'autofocus="' + oParams.oAutofocus + '" '; }
        if (oParams.oAutocomplete) { objString += 'autocomplete="' + oParams.oAutocomplete + '" '; }

        if (oParams.oSize) { objString += 'size="' + oParams.oSize + '" '; }
        if (oParams.maxSize) { objString += 'maxlength="' + oParams.maxSize + '" '; }

        // Textarea specific
        if (oParams.oRows) { objString += 'rows="' + oParams.oRows + '" '; }
        if (oParams.oCols) { objString += 'cols="' + oParams.oCols + '" '; }

        // Label Specific
        if (oParams.oFor) { objString += 'for="' + oParams.oFor + '" '; }

        // Checkbox, Radio Button, Select
        if (oParams.os === 'Y') {
            objString += 'checked="checked" ';
            objString += 'selected="selected" ';
        }

        if (oParams.oSrc) { objString += 'src="' + oParams.oSrc + '" '; }
        if (oParams.oScroll) { objString += 'scrolling="'+oParams.oScroll+'" '; }
        if (oParams.oTarget) { objString += 'target="'+oParams.oTarget+'" '; }
        if (oParams.oHref) { objString += 'href="'+oParams.oHref+'" '; }

        if (oParams.oWidth) { objString += 'width="' + oParams.oWidth + '" '; }
        if (oParams.oHeight) { objString += 'height="' + oParams.oHeight + '" '; }
        if (oParams.oPad) { objString += 'cellpadding="' + oParams.oPad + '" '; }
        if (oParams.oSpace) { objString += 'cellspacing="' + oParams.oSpace + '" '; }
        if (oParams.oBorder)    { objString += 'border="' + oParams.oBorder + '" '; }

        if (oParams.oAlign) { objString += 'align="' + oParams.oAlign + '" '; }
        if (oParams.vAlign) { objString += 'valign="' + oParams.vAlign + '" '; }
        if (oParams.nWrap) { objString += 'nowrap="nowrap" '; }
        if (oParams.cSpan) { objString += 'colspan="' + oParams.cSpan + '" '; }
        if (oParams.rSpan) { objString += 'rowspan="' + oParams.rSpan + '" '; }

        //vReadOnly = (oParams.readOnly === 'Y') ? 'Y' : 'N';
        //vIsDisabled = (oParams.isDisabled === 'Y') ? 'Y' : 'N';
        //disableJS = (vReadOnly === 'Y' || vIsDisabled === 'Y') ? 'Y' : 'N';

        //if (vReadOnly === 'Y') { objString += 'readonly="readonly" '; }
        //if (vIsDisabled === 'Y') { objString += 'disabled="disabled" '; }
        //if (disableJS === 'N')  {
            if (oParams.oClick) { objString += dhf.fmmjcIE('onclick', oParams.oClick); }
            if (oParams.oFocus) { objString += dhf.fmmjcIE('onfocus', oParams.oFocus); }
            if (oParams.oBlur) { objString += dhf.fmmjcIE('onblur', oParams.oBlur); }
            if (oParams.oChange) { objString += dhf.fmmjcIE('onchange', oParams.oChange); }
            if (oParams.okeyUp) { objString += dhf.fmmjcIE('onkeyup', oParams.okeyUp); }
            if (oParams.oMouseOver) { objString += dhf.fmmjcIE('onmouseover', oParams.oMouseOver); }
            if (oParams.oMouseOut) { objString += dhf.fmmjcIE('onmouseout', oParams.oMouseOut); }
            if (oParams.oMouseDown) { objString += dhf.fmmjcIE('onmousedown', oParams.oMouseDown); }
        //}
        return objString;
    },


    /**
     * Strips leading and trailing white space from a string
     * @param string inString
     * @return string
     */
    trim: function (inString) {
        'use strict';
        var trimString = inString;

        if (typeof inString === 'string') { trimString = inString.replace(/^\s+|\s+$/g, ''); }
        return trimString;
    },

    /**
     * Clears and repopulates an HTML select menu with new options
     * @param string oid - DOM element Id
     * @param array optionArray - Array of JSON objects
     * @return void
     */
    usm: function (oid, optionArray, callback) {
        'use strict';
        var domObj,
            selectedValue,
            objClass,
            ol,
            optionObj,
            singleObj,
            objString,
            i = 0;

        domObj = $D(oid);
        selectedValue = dhf.trim(domObj.value);
        domObj.options.length = 0;
        while (domObj.childNodes.length > 0) { domObj.removeChild(domObj.childNodes[0]); }

        if (optionArray.length === 0) { optionArray = [{ ol: 'No Values', ov: '', os: 'N' }]; }
        for (i = 0; i < optionArray.length; i += 1) {

            singleObj = optionArray[i];
            ol = singleObj.ol || singleObj.ov;
            objClass = singleObj.ocn || '';

            if (singleObj.og) {
				optionObj = document.createElement('optGroup');
				optionObj.setAttribute('label', singleObj.og);
				if (singleObj.oid) { optionObj.setAttribute('id', oid+'_'+singleObj.oid); }
				if (objClass) { optionObj.setAttribute('class', objClass); }
				domObj.appendChild(optionObj);
            }
            else {
                if (singleObj.os === 'Y' && !selectedValue) { selectedValue = singleObj.ov; }
                optionObj = document.createElement('option');
                optionObj.setAttribute('value', singleObj.ov);
				if (objClass) { optionObj.setAttribute('class', objClass); }
                if (singleObj.gid && $D(oid+'_'+singleObj.gid)) { $D(oid+'_'+singleObj.gid).appendChild(optionObj); }
                else { domObj.appendChild(optionObj); }
                optionObj.text = ol;
            }
        }
        domObj.value = selectedValue;
        if (domObj.selectedIndex < 0) { domObj.selectedIndex = 0; }
        // Allow a callback function to be passed in and executed
        if (callback && (typeof callback === 'function')) { callback(); }
        return;
    },

    /**
     * Build a string if the object is having more than one JS function per event type
     */
    fmmjc: function (jsActionMethods) {
        'use strict';
        var returnString = '',
            i = 0;

        if (typeof jsActionMethods === 'string') { returnString = jsActionMethods; }
        else if (typeof jsActionMethods === 'object') {
            for (i = 0; i < jsActionMethods.length; i += 1) { returnString += jsActionMethods[i].methodName+"("+jsActionMethods[i].methodVariables+"); ";  }
        }
        return returnString;
    },

    /**
     * Build a string if the object is having more than one JS function per event type.
     * But for IE Only
     */
    fmmjcIE: function (actionName, jsActionMethods) {
        'use strict';
        var returnString = "",
            i = 0;

        if (jsActionMethods !== '') {
            if (typeof jsActionMethods === 'string') { returnString = ' '+actionName+'="'+jsActionMethods+'"'; }
            else if (typeof jsActionMethods === 'object') {
                returnString = ' '+actionName+'="';
                for (i = 0; i < jsActionMethods.length; i += 1) { returnString += jsActionMethods[i].methodName +'('+jsActionMethods[i].methodVariables+'); '; }
                returnString += '"';
            }
        }
        return returnString;
    },

    makeInputText: function (oParams) {
        'use strict';
        var attribute,
            objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        for (attribute in dhf.defaultInputText) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultInputText[attribute]; } }

        objString = '<input type="text" ';
        objString += dhf.addParamsIE(oParams);
        objString += '/>';

        try { domObj = document.createElement(objString); }
        catch (e) { domObj = dhf.addParams('input', oParams, 'text'); }

        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeInputNumber: function (oParams) {
        'use strict';
        var attribute,
            objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        for (attribute in dhf.defaultInputNumber) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultInputNumber[attribute]; } }

        objString = '<input type="number" ';
        objString += dhf.addParamsIE(oParams);
        objString += '/>';

        try { domObj = document.createElement(objString); }
        catch (e) { domObj = dhf.addParams('input', oParams, 'number'); }

        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeInputSearch: function (oParams) {
        'use strict';
        var attribute,
            objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        for (attribute in dhf.defaultInputSearch) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultInputSearch[attribute]; } }

        objString = '<input type="search" ';
        objString += dhf.addParamsIE(oParams);
        objString += '/>';

        try { domObj = document.createElement(objString); }
        catch (e) { domObj = dhf.addParams('input', oParams, 'search'); }

        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeInputTel: function (oParams) {
        'use strict';
        var attribute,
            objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        for (attribute in dhf.defaultInputTel) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultInputTel[attribute]; } }

        objString = '<input type="tel" ';
        objString += dhf.addParamsIE(oParams);
        objString += '/>';

        try { domObj = document.createElement(objString); }
        catch (e) { domObj = dhf.addParams('input', oParams, 'tel'); }

        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeInputUrl: function (oParams) {
        'use strict';
        var attribute,
            objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        for (attribute in dhf.defaultInputUrl) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultInputUrl[attribute]; } }

        objString = '<input type="url" ';
        objString += dhf.addParamsIE(oParams);
        objString += '/>';

        try { domObj = document.createElement(objString); }
        catch (e) { domObj = dhf.addParams('input', oParams, 'url'); }

        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeInputEmail: function (oParams) {
        'use strict';
        var attribute,
            objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        for (attribute in dhf.defaultInputEmail) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultInputEmail[attribute]; } }

        objString = '<input type="email" ';
        objString += dhf.addParamsIE(oParams);
        objString += '/>';

        try { domObj = document.createElement(objString); }
        catch (e) { domObj = dhf.addParams('input', oParams, 'email'); }

        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeInputPassword: function (oParams) {
        'use strict';
        var attribute,
            objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        for (attribute in dhf.defaultInputPassword) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultInputPassword[attribute]; } }

        objString = '<input type="password" ';
        objString += dhf.addParamsIE(oParams);
        objString += '/>';

        try { domObj = document.createElement(objString); }
        catch (e) { domObj = dhf.addParams('input', oParams, 'password'); }

        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeInputTextArea: function (oParams) {
        'use strict';
        var attribute,
            objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        for (attribute in dhf.defaultInputTextArea) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultInputTextArea[attribute]; } }

        if (oParams.objRows !== undefined) { oParams.oRows = oParams.objRows; }
        if (oParams.objColumns !== undefined) { oParams.oCols = oParams.objColumns; }

        objString = '<textarea ';
        objString += dhf.addParamsIE(oParams);
        objString += '></textarea>';

        try { domObj = document.createElement(objString); }
        catch (e) { domObj = dhf.addParams('textarea', oParams); }

        if (oParams.iHTML) { domObj.innerHTML = oParams.iHTML; }
        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeInputHidden: function (oParams) {
        'use strict';
        var objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }

        objString = '<input type="hidden" ';
        objString += dhf.addParamsIE(oParams);
        objString += '/>';

        try { domObj = document.createElement(objString); }
        catch (e) { domObj = dhf.addParams('input', oParams, 'hidden'); }

        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeInputButton: function (oParams) {
        'use strict';
        var attribute,
            objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        for (attribute in dhf.defaultInputButton) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultInputButton[attribute]; } }

        objString = '<input type="button" ';
        objString += dhf.addParamsIE(oParams);
        objString += '/>';

        try { domObj = document.createElement(objString); }
        catch (e) { domObj = dhf.addParams('input', oParams, 'button'); }

        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeInputSubmit: function (oParams) {
        'use strict';
        var attribute,
            objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        for (attribute in dhf.defaultInputSubmit) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultInputSubmit[attribute]; } }

        objString = '<input type="submit" ';
        objString += dhf.addParamsIE(oParams);
        objString += '/>';

        try { domObj = document.createElement(objString); }
        catch (e) { domObj = dhf.addParams('input', oParams, 'submit'); }

        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeInputCheckbox: function (oParams) {
        'use strict';
        var attribute,
            objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        for (attribute in dhf.defaultInputCheckbox) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultInputCheckbox[attribute]; } }

        objString = '<input type="checkbox" ';
        objString += dhf.addParamsIE(oParams);
        objString += '/>';

        try { domObj = document.createElement(objString); }
        catch (e) { domObj = dhf.addParams('input', oParams, 'checkbox'); }

        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeInputLabel: function (oParams) {
        'use strict';
        var attribute,
            objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        for (attribute in dhf.defaultInputLabel) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultInputLabel[attribute]; } }

        objString = '<label ';
        objString += dhf.addParamsIE(oParams);
        objString += '></label>';

        try { domObj = document.createElement(objString); }
        catch (e) { domObj = dhf.addParams('label', oParams); }

        if (oParams.iHTML) { domObj.innerHTML = oParams.iHTML; }
        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeInputRadio: function (oParams) {
        'use strict';
        var attribute,
            objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        for (attribute in dhf.defaultInputRadio) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultInputRadio[attribute]; } }

        objString = '<input type="radio" ';
        objString += dhf.addParamsIE(oParams);
        objString += '/>';

        try { domObj = document.createElement(objString); }
        catch (e) { domObj = dhf.addParams('input', oParams, 'radio'); }

        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeButtonButton: function (oParams) {
        'use strict';
        var attribute,
            objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        for (attribute in dhf.defaultButtonButton) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultButtonButton[attribute]; } }

        if (!oParams.ol) {
            if (oParams.objLabel) { oParams.ol = oParams.objLabel; }
            else if (oParams.oValue) { oParams.ol = oParams.oValue; }
            else if (oParams.iHTML) { oParams.ol = oParams.iHTML; }
            else { oParams.ol = 'Button'; }
        }

        objString = '<button type="button" ';
        objString += dhf.addParamsIE(oParams);
        objString += '></button>';

        try { domObj = document.createElement(objString); }
        catch (e) { domObj = dhf.addParams('button', oParams, 'button'); }

        domObj.innerHTML = oParams.ol;
        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeInputSelect: function (oParams) {
        'use strict';
        var attribute,
            objString,
            domObj,
            ol,
            optionObj,
            optionsArray,
            i = 0;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        for (attribute in dhf.defaultInputSelect) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultInputSelect[attribute]; } }

        objString = '<select ';
        objString += dhf.addParamsIE(oParams);
        objString += '></select>';

        /* Try to create object for explorer else the regular way   */
        try { domObj = document.createElement(objString); }
        catch (e) { domObj = dhf.addParams('select', oParams); }

        domObj.options.length = 0;
        /*  If an array of objects was not passed in for option values, put in dummy data   */
        if (oParams.optionsArray && dhf.util.toType(oParams.optionsArray) === 'array') { optionsArray = oParams.optionsArray; }
        else { optionsArray = [{ "ol": 'No Data', "ov": '', "os": 'N' }]; }
        for (i = 0; i < optionsArray.length; i += 1) {
        	ol = optionsArray[i].ol || optionsArray[i].ov;
        	optionObj = document.createElement('option');
        	optionObj.setAttribute('value', optionsArray[i].ov);
        	domObj.appendChild(optionObj);
        	optionObj.text = ol;
        }
        if (oParams.oValue) { domObj.value = oParams.oValue; }
        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    /*  Begin Table Functions   */
    makeTable: function (oParams) {
        'use strict';
        var attribute,
            objString,
            tBodyObj,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        for (attribute in dhf.defaultTable) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultTable[attribute]; } }

        tBodyObj = dhf.makeTableBody(oParams.tBodyId);
        objString = '<table ';
        objString += dhf.addParamsIE(oParams);
        objString += '></table>';

        try { domObj = document.createElement(objString); }
        catch(e) { domObj = dhf.addParams('table', oParams); }

        if (oParams.iObjs && dhf.util.toType(oParams.iObjs) === 'array') { dhf.makeSubObjects(tBodyObj, oParams.iObjs); }
        domObj.appendChild(tBodyObj);

        if (oParams.parentId) {
            if (oParams.parentId === 'documentBody') { document.body.appendChild(domObj); }
            else if ($D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        }
        return domObj;
    },

    makeTableBody: function (tBodyId) {
        'use strict';
        var tBodyObj;

        if (tBodyId) {
            try { tBodyObj = document.createElement('<tbody id="'+tBodyId+'"></tbody>'); }
            catch(e) {
                tBodyObj = document.createElement('tbody');
                tBodyObj.setAttribute('id', tBodyId);
            }
        }
        else {
            try { tBodyObj = document.createElement('<tbody></tbody>'); }
            catch(ee) { tBodyObj = document.createElement('tbody'); }
        }
        return tBodyObj;
    },

    make_tBody: function (oParams) {
        'use strict';
        var objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        objString = '<tbody ';
        objString += dhf.addParamsIE(oParams);
        objString += '></tbody>';

        try { domObj = document.createElement(objString); }
        catch(e) { domObj = dhf.addParams('tbody', oParams); }

        if (oParams.iObjs && dhf.util.toType(oParams.iObjs) === 'array') { dhf.makeSubObjects(domObj, oParams.iObjs); }
        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeTableHeader: function (oParams) {
        'use strict';
        var objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        objString = '<thead ';
        objString += dhf.addParamsIE(oParams);
        objString += '></thead>';

        try { domObj = document.createElement(objString); }
        catch(e) { domObj = dhf.addParams('thead', oParams); }

        if (oParams.iObjs && dhf.util.toType(oParams.iObjs) === 'array') { dhf.makeSubObjects(domObj, oParams.iObjs); }
        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeTableFooter: function (oParams) {
        'use strict';
        var objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        objString = '<tfoot ';
        objString += dhf.addParamsIE(oParams);
        objString += '></tfoot>';

        try { domObj = document.createElement(objString); }
        catch(e) { domObj = dhf.addParams('tfoot', oParams); }

        if (oParams.iObjs && dhf.util.toType(oParams.iObjs) === 'array') { dhf.makeSubObjects(domObj, oParams.iObjs); }
        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    altRowColor: function () {
        'use strict';
        var rowClass = '';

        dhf.rowColor.increment += 1;
        if (dhf.util.validateInteger(dhf.rowColor.increment / 2) === true) { rowClass = 'row_color1'; }
        else { rowClass = 'row_color2'; }
        return rowClass;
    },

    makeTableRow: function (oParams) {
        'use strict';
        var attribute,
            objString,
            domObj,
            originalValue;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        for (attribute in dhf.defaultTableRow) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultTableRow[attribute]; } }

        if (oParams.altColor) {
            if (oParams.ocn) {
                originalValue = oParams.ocn;
                oParams.ocn = dhf.altRowColor()+' '+originalValue;
            }
            else { oParams.ocn = dhf.altRowColor(); }
        }

        objString = '<tr ';
        objString += dhf.addParamsIE(oParams);
        objString += '></tr>';

        try { domObj = document.createElement(objString); }
        catch(e) { domObj = dhf.addParams('tr', oParams); }

        if (oParams.iObjs && dhf.util.toType(oParams.iObjs) === 'array') { dhf.makeSubObjects(domObj, oParams.iObjs); }
        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeTableCell: function (oParams) {
        'use strict';
        let attribute;
        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        for (attribute in dhf.defaultTableCell) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultTableCell[attribute]; } }

        const domObj = dhf.addParams('td', oParams);
        if (oParams.iHTML) { domObj.innerHTML = oParams.iHTML; }
        if (oParams.iObjs && dhf.util.toType(oParams.iObjs) === 'array') { dhf.makeSubObjects(domObj, oParams.iObjs); }
        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

	// cn = Cell Name
	// sk = Sort Key
	// sa = Sort Attribute
	// ct = Cell Title
	makeSortHeaderCell: function (cn, sk, sa, ct) {
		'use strict';
		var hCell = {
			"iFunctName": "makeTableCell",
			"iParams": {
				"oid": sk+"Header",
				"iHTML": cn
				//"oAttr": { "oName": ((sa) ? sa : "data-sortby"), "oValue": sk }
			}
		};

		if (sk) { hCell.iParams.oAttr = { "oName": ((sa) ? sa : "data-sortby"), "oValue": sk }; }
		if (ct) { hCell.iParams.oTitle = ct; }
		return hCell;
	},

	makeIobjsCell: function (iHTML, ocn, oTitle) {
		'use strict';
		var iCell = { "iFunctName": "makeTableCell", "iParams": { "iHTML": iHTML } };

		if (ocn) { iCell.iParams.ocn = ocn; }
		if (oTitle) { iCell.iParams.oTitle = oTitle; }

		return iCell;
	},

    makeDiv: function (oParams) {
        'use strict';
        var attribute,
            objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        for (attribute in dhf.defaultDiv) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultDiv[attribute]; } }

        objString = '<div ';
        objString += dhf.addParamsIE(oParams);
        objString += '></div>';

        try { domObj = document.createElement(objString); }
        catch(e) { domObj = dhf.addParams('div', oParams); }

        if (oParams.iHTML) { domObj.innerHTML = oParams.iHTML; }
        if (oParams.iObjs && dhf.util.toType(oParams.iObjs) === 'array') { dhf.makeSubObjects(domObj, oParams.iObjs); }
        if (oParams.parentId) {
            if (oParams.parentId === 'documentBody') { document.body.appendChild(domObj); }
            else if ($D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        }
        return domObj;
    },

    makeIframe: function (oParams) {
        'use strict';
        var attribute,
            objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        for (attribute in dhf.defaultIframe) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultIframe[attribute]; } }

        objString = '<iframe ';
        objString += dhf.addParamsIE(oParams);
        objString += '></iframe>';

        try { domObj = document.createElement(objString); }
        catch(e) { domObj = dhf.addParams('iframe', oParams); }

        if (oParams.iObjs && dhf.util.toType(oParams.iObjs) === 'array') { dhf.makeSubObjects(domObj, oParams.iObjs); }

        if (oParams.parentId) {
            if (oParams.parentId === 'documentBody') { document.body.appendChild(domObj); }
            else if ($D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        }
        return domObj;
    },

    makeSpan: function (oParams) {
        'use strict';
        var attribute,
            objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        for (attribute in dhf.defaultSpan) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultSpan[attribute]; } }

        objString = '<span ';
        objString += dhf.addParamsIE(oParams);
        objString += '></span>';

        try { domObj = document.createElement(objString); }
        catch(e) { domObj = dhf.addParams('span', oParams); }

        if (oParams.iHTML) { domObj.innerHTML = oParams.iHTML; }
        if (oParams.iObjs && dhf.util.toType(oParams.iObjs) === 'array') { dhf.makeSubObjects(domObj, oParams.iObjs); }
        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeAnchor: function (oParams) {
        'use strict';
        var attribute,
            objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        for (attribute in dhf.defaultAnchor) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultAnchor[attribute]; } }

        objString = '<a ';
        objString += dhf.addParamsIE(oParams);
        objString += '></a>';

        try { domObj = document.createElement(objString); }
        catch(e) { domObj = dhf.addParams('a', oParams); }

        if (oParams.iHTML) { domObj.innerHTML = oParams.iHTML; }
        if (oParams.iObjs && dhf.util.toType(oParams.iObjs) === 'array') { dhf.makeSubObjects(domObj, oParams.iObjs); }
        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeMailto: function (oParams) {
        'use strict';
        var attribute,
            objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        for (attribute in dhf.defaultMailto) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultMailto[attribute]; } }

        objString = '<a href="mailto:'+oParams.to+'"></a>';

        try { domObj = document.createElement(objString); }
        catch(e) {
        	domObj = document.createElement('a');
        	domObj.setAttribute('href', 'mailto:'+oParams.to);
        }

        if (oParams.iHTML) { domObj.innerHTML = oParams.iHTML; }
        if (oParams.iObjs && dhf.util.toType(oParams.iObjs) === 'array') { dhf.makeSubObjects(domObj, oParams.iObjs); }
        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeParagraph: function (oParams) {
        'use strict';
        var attribute,
            objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }

        if (dhf.defaultParagraph && dhf.util.objectSize(dhf.defaultParagraph) > 0) {
            for (attribute in dhf.defaultParagraph) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultParagraph[attribute]; } }
        }

        objString = '<p ';
        objString += dhf.addParamsIE(oParams);
        objString += '></p>';

        try { domObj = document.createElement(objString); }
        catch(e) { domObj = dhf.addParams('p', oParams); }

        if (oParams.iHTML) { domObj.innerHTML = oParams.iHTML; }
        if (oParams.iObjs && dhf.util.toType(oParams.iObjs) === 'array') { dhf.makeSubObjects(domObj, oParams.iObjs); }
        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeUL: function (oParams) {
        'use strict';
        var objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }
        objString = '<ul ';
        objString += dhf.addParamsIE(oParams);
        objString += '></ul>';

        try { domObj = document.createElement(objString); }
        catch(e) { domObj = dhf.addParams('ul', oParams); }

        if (oParams.iObjs && dhf.util.toType(oParams.iObjs) === 'array') { dhf.makeSubObjects(domObj, oParams.iObjs); }
        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeLI: function (oParams) {
        'use strict';
        var objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { oParams = {}; }

        objString = '<li ';
        objString += dhf.addParamsIE(oParams);
        objString += '></li>';

        try { domObj = document.createElement(objString); }
        catch(e) { domObj = dhf.addParams('li', oParams); }

        if (oParams.iHTML) { domObj.innerHTML = oParams.iHTML; }
        if (oParams.iObjs && dhf.util.toType(oParams.iObjs) === 'array') { dhf.makeSubObjects(domObj, oParams.iObjs); }
        if (oParams.parentId && $D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        return domObj;
    },

    makeImage: function (oParams) {
        'use strict';
        var attribute,
            objString,
            domObj;

        if (!oParams || dhf.util.objectSize(oParams) === 0) { throw new Error("No Paramters Passed In"); }
        for (attribute in dhf.defaultImage) { if (oParams[attribute] === undefined) { oParams[attribute] = dhf.defaultImage[attribute]; } }

        if (!oParams.oSrc) { return document.createElement('span'); }

        objString = '<img ';
        objString += dhf.addParamsIE(oParams);
        objString += '/>';

        try { domObj = document.createElement(objString); }
        catch(e) { domObj = dhf.addParams('img', oParams); }
        if (oParams.parentId) {
            if (oParams.parentId === 'documentBody') { document.body.appendChild(domObj); }
            else if ($D(oParams.parentId)) { $D(oParams.parentId).appendChild(domObj); }
        }
        return domObj;
    }

};



dhf.buffer = {
    master: null,
    tasks: [],

    handleNextTask: function () {
        'use strict';
        var b = dhf.buffer,
            task;

        if (b.tasks.length > 0) {
            Logger.log();
            task = b.tasks.shift();
            task(dhf.buffer.handleNextTask);
        }
        else { b.master(); }
    },

    append: function (task) {
        'use strict';
        dhf.buffer.tasks.push(task);
    },

    clear: function () {
        'use strict';
        dhf.buffer.master = null;
        dhf.buffer.tasks = [];
    },

    setMaster: function (handler) {
        'use strict';
        dhf.buffer.master = handler;
    }
};


dhf.errorHandler = {

    errStack: [],
    config: {},
    dialogDefault: { modal: true, dialogClass: 'alert', title: 'The following errors found:' },

    setup: function (params) {
        'use strict';
        var eh = dhf.errorHandler, a;

        if (dhf.util.objectSize(eh.config) < 1) {
            for (a in eh.dialogDefault) { eh.config[a] = eh.dialogDefault[a]; }
        }
        if (params && typeof params === 'object') {
            if (params.autoOpen) { eh.config.autoOpen = params.autoOpen; }
            if (params.width) { eh.config.width = params.width; }
            if (params.minWidth) { eh.config.minWidth = params.minWidth; }
            if (params.maxWidth) { eh.config.maxWidth = params.maxWidth; }
            if (params.height) { eh.config.height = params.height; }
            if (params.minHeight) { eh.config.minHeight = params.minHeight; }
            if (params.maxHeight) { eh.config.maxHeight = params.maxHeight; }
            if (params.modal) { eh.config.modal = params.modal; }
            if (params.position) { eh.config.position = params.position; }
            if (params.title) { eh.config.title = params.title; }
            if (params.dialogClass) { eh.config.dialogClass = params.dialogClass; }
            if (params.zIndex) { eh.config.zIndex = params.zIndex; }
            if (params.stack) { eh.config.stack = params.stack; }
            if (params.buttons && typeof params.buttons === 'object') { eh.config.buttons = params.buttons; }
        }
    },

    // setErrorMessage
    add: function(errorMessage) {
        'use strict';
        var eh = dhf.errorHandler;

        if (errorMessage !== '' && jQuery.inArray(errorMessage, eh.errStack) === -1) {
            eh.errStack.push(errorMessage);
            Logger.log("Adding Error Message: "+errorMessage);
        }
    },

    addMessage: function (errorMessage) {
        'use strict';
        dhf.errorHandler.add(errorMessage);
    },

    // displayFormValidationErrors
    display: function(params) {
        'use strict';
        var i = 0,
            returnValue = false,
            eh = dhf.errorHandler,
            popup = dhf.popup;

        eh.setup(params);
        if (eh.errStack.length > 0) {
            dhf.util.removeWaitDiv();
            returnValue = true;
            popup.make(eh.config);
            for (i = 0; i < eh.errStack.length; i += 1) { popup.appendErrorLine(eh.errStack[i], 'BANG'); }
            popup.open();
        }
        eh.errStack = [];
        eh.config = {};
        return returnValue;
    },

    displayErrors: function (params) {
        'use strict';
        dhf.errorHandler.display(params);
    },

    displaySingleLine: function (errorString) {
        'use strict';
        dhf.util.removeWaitDiv();
        dhf.popup.make((dhf.util.objectSize(dhf.errorHandler.config) < 1) ? dhf.errorHandler.dialogDefault : dhf.errorHandler.config);
        dhf.popup.appendErrorLine(errorString, 'BANG');
        dhf.popup.open();
        dhf.errorHandler.errStack = [];
        dhf.errorHandler.config = {};
        return true;
    },

    clear: function () {
        'use strict';
        dhf.errorHandler.errStack = [];
    },

    // Alias
    appendErrorLine: function (errorText, imageType) {
        'use strict';
        dhf.popup.appendErrorLine(errorText, imageType);
    }

};


dhf.popup = {

    dialogBox: null,
    config: {},
    dialogDefault: {
        autoOpen: false,
        width: 350,
        minWidth: 200,
        maxWidth: 600,
        minHeight: 200,
        maxHeight: 800,
        position: 'center',
        title: 'The following errors found:',
        close: function() { jQuery(this).dialog('destroy').remove(); }
    },

    setup: function (params) {
        'use strict';
        var popup = dhf.popup,
        	a;

        if (dhf.util.objectSize(popup.config) < 1) {
            Logger.logGroupCollapsed("Popup Params:");
            for (a in popup.dialogDefault) {
                Logger.log("'"+a+"' Value: '"+popup.dialogDefault[a]+"'");
                popup.config[a] = popup.dialogDefault[a];
            }
            Logger.logGroupEnd("Popup Params:");
        }
        if (params && typeof params === 'object') {
            if (params.autoOpen) { popup.config.autoOpen = params.autoOpen; }
            if (params.width) { popup.config.width = params.width; }
            if (params.minWidth) { popup.config.minWidth = params.minWidth; }
            if (params.maxWidth) { popup.config.maxWidth = params.maxWidth; }
            if (params.height) { popup.config.height = params.height; }
            if (params.minHeight) { popup.config.minHeight = params.minHeight; }
            if (params.maxHeight) { popup.config.maxHeight = params.maxHeight; }
            if (params.modal) { popup.config.modal = params.modal; }
            if (params.position) { popup.config.position = params.position; }
            if (params.title) { popup.config.title = params.title; }
            if (params.dialogClass) { popup.config.dialogClass = params.dialogClass; }
            if (params.zIndex) { popup.config.zIndex = params.zIndex; }
            if (params.stack) { popup.config.stack = params.stack; }
            if (params.buttons && typeof params.buttons === 'object') { popup.config.buttons = params.buttons; }
            if (params.close) { popup.config.close = params.close; }
        }
    },

    make: function (params) {
        'use strict';
        var popup = dhf.popup;

        popup.setup(params);
        if ($D('warningErrorHelpBody')) { jQuery('#warningErrorHelpBody').remove(); }
        popup.dialogBox = jQuery('<div id="warningErrorHelpBody"></div>').appendTo('body');
        popup.dialogBox.dialog(popup.config);
        popup.config = {};
    },

    html: function (text) {
        'use strict';
        dhf.popup.dialogBox.html(text);
    },

    htmlOpen: function (text) {
        'use strict';
        dhf.util.removeWaitDiv();
        dhf.popup.dialogBox.html(text).dialog('open');
        dhf.popup.config = {};
    },

    open: function () {
        'use strict';
        dhf.util.removeWaitDiv();
        dhf.popup.dialogBox.dialog('open');
        if (dhf.popup.config.dialogClass === 'alert') { jQuery(".ui-widget-overlay").addClass("alert"); }
        else { jQuery(".ui-widget-overlay").removeClass("alert"); }
        dhf.popup.config = {};
    },

    appendErrorLine: function (errorText, imageType) {
        'use strict';
        if (imageType === 'INFO') {
            dhf.makeDiv({
                ocn: 'popup-entry',
                parentId: 'warningErrorHelpBody',
                iObjs: [{ iFunctName: 'makeDiv', iParams: { ocn: 'popup-entry-info', iHTML: errorText } }]
            });
        }
        else {
            dhf.makeDiv({
                ocn: 'popup-entry',
                parentId: 'warningErrorHelpBody',
                iObjs: [{ iFunctName: 'makeDiv', iParams: { ocn: 'popup-entry-error', iHTML: errorText } }]
            });
        }
    },

    appendAvailableApplicationLine: function(appText) {
        'use strict';
        dhf.makeDiv({
            ocn: 'popup-entry',
            parentId: 'warningErrorHelpBody',
            iHTML: appText
        });
    }
};


dhf.util = {

    // Pass in a formatted string and get a JS Date object back.
    dateParse: function (inString, opt) {

        'use strict';

        var dateObj = new Date(),
            dateParts = [];

        opt = opt || { format: 'm/d/Y', offset: false };
        opt.format = (!opt.format) ? 'm/d/Y' : opt.format;
        opt.offset = (!opt.offset) ? false : opt.offset;

        if (!inString) { dateObj = new Date(); }

        else if (opt.format === 'm/d/Y') {
            dateParts = inString.match(/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/);
            dateObj = new Date(parseInt(dateParts[3], 10), (dhf.util.round(dateParts[1]) -1), parseInt(dateParts[2], 10));
        }

        else if (opt.format === 'Y-m-d') {
            dateParts = inString.match(/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/);
            dateObj = new Date(parseInt(dateParts[1], 10), (dhf.util.round(dateParts[2]) -1), parseInt(dateParts[3], 10));
        }

        if (opt.offset === 'A') { dateObj = new Date(dateObj.getTime() + (dateObj.getTimezoneOffset() * 60 * 1000)); }
        else if (opt.offset === 'S') { dateObj = new Date(dateObj.getTime() - (dateObj.getTimezoneOffset() * 60 * 1000)); }

        return dateObj;
    },


    dateFormatOld: function (dateObj, format) {

        'use strict';

        var dateString = '01/01/2012',
            year = '2012',
            month = '01',
            day = '01';

        if (!dateObj || dhf.util.toType(dateObj) !== 'date') { dateObj = new Date(); }
        format = format || 'm/d/Y';

        month = (dateObj.getMonth() + 1);
        day = dateObj.getDate();

        if (format === 'm/d/Y') {
            year = dateObj.getFullYear().toString();

            month = (month < 10) ? '0'+month.toString() : month.toString();

            day = (day < 10) ? '0'+day.toString() : day.toString();

            dateString = month+'/'+day+'/'+year;
        }

        else if (format === 'Y-m-d') {
            year = dateObj.getFullYear().toString();

            month = (month < 10) ? '0'+month.toString() : month.toString();

            day = (day < 10) ? '0'+day.toString() : day.toString();

            dateString = year+'-'+month+'-'+day;
        }

        return dateString;

    },

    // Passing a JS Date object and get a formatted string back.
    dateFormat: function (inDate, opt) {

        'use strict';

        var dateObj,
        	dateString = '',
            year = '2012',
            month = 0,
            day = 0,
            hour = 0,
            minute = '00',
            second = '00',
            i = 0,
            c = '',
            escapeHolder = false,
            dayNamesShort = ['Sun','Mon','Tue','Wed','Thur','Fri','Sat'],
            dayNamesLong = ['Sunday','Monday','TuesDay','Wednesday','Thursday','Friday','Saturday'],
            monthNamesShort = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
            monthNamesLong = ['January','February','March','April','May','June','July','August','September','October','November','December'];

        if (inDate) {
            if (dhf.util.toType(inDate) === 'date') { dateObj = inDate; }
            else if (dhf.util.toType(inDate) === 'number' && dhf.util.validateInteger(inDate)) { dateObj = new Date(inDate); }
            else { dateObj = new Date(); }
        }
        else { dateObj = new Date(); }

        opt = opt || { format: 'm/d/Y', offset: false };
        opt.format = (!opt.format) ? 'm/d/Y' : opt.format;
        opt.offset = (!opt.offset) ? false : opt.offset;

        if (opt.offset === 'A') { dateObj = new Date(dateObj.getTime() + (dateObj.getTimezoneOffset() * 60 * 1000)); }
        else if (opt.offset === 'S') { dateObj = new Date(dateObj.getTime() - (dateObj.getTimezoneOffset() * 60 * 1000)); }

        year = dateObj.getFullYear().toString();
        month = (dateObj.getMonth() + 1);
        day = dateObj.getDate();
        hour = dateObj.getHours();
        minute = (dateObj.getMinutes() < 10) ? '0'+(dateObj.getMinutes()).toString() : (dateObj.getMinutes()).toString();
        second = (dateObj.getSeconds() < 10) ? '0'+(dateObj.getSeconds()).toString() : (dateObj.getSeconds()).toString();

        for (i = 0; i < opt.format.length; i += 1) {

            c = opt.format.charAt(i).toString();

            if (!escapeHolder) {

                // If an escape character, then don't append anything.
                if (c === String.fromCharCode(92)) { escapeHolder = true; }

                // Days Codes
                // Day of the month, 2 digits with leading zeros - 01 to 31
                else if (c === 'd') { dateString += (day < 10) ? '0'+day.toString() : day.toString(); }

                // A textual representation of a day, three letters - Mon through Sun
                else if (c === 'D') { dateString += dayNamesShort[dateObj.getDay()]; }

                // Day of the month without leading zeros - 1 to 31
                else if (c === 'j') { dateString += day.toString(); }

                // A full textual representation of the day of the week - Sunday through Saturday
                else if (c === 'l') { dateString += dayNamesLong[dateObj.getDay()]; }

                // Numeric representation of the day of the week - 0 (for Sunday) through 6 (for Saturday)
                else if (c === 'w') { dateString += dateObj.getDay().toString(); }


                // Month Codes
                // A full textual representation of a month, such as January or March - January through December
                else if (c === 'F') { dateString += monthNamesLong[month - 1]; }

                // Numeric representation of a month, with leading zeros - 01 through 12
                else if (c === 'm') { dateString += (month < 10) ? '0'+month.toString() : month.toString(); }

                // A short textual representation of a month, three letters - Jan through Dec
                else if (c === 'M') { dateString += monthNamesShort[month - 1]; }

                // Numeric representation of a month, without leading zeros - 1 through 12
                else if (c === 'n') { dateString += month.toString(); }

                // Number of days in the given month - 28 through 31
                else if (c === 't') { dateString += (32 - (new Date(dateObj.getFullYear(), dateObj.getMonth(), 32)).getDate()).toString(); }


                // Year Codes
                // Whether it's a leap year - 1 if it is a leap year, 0 otherwise
                //else if (c === 'L') { dateString += ((!(y & 3 || y & 15 && !(y % 25))) ? 1 : 0).toString(); }

                // A full numeric representation of a year, 4 digits - Examples: 1999 or 2003
                else if (c === 'Y') { dateString += year; }

                // A two digit representation of a year - Examples: 99 or 03
                else if (c === 'y') { dateString += year.substring(2,4); }


                // Time settings
                // Lowercase Ante meridiem and Post meridiem - am or pm
                else if (c === 'a') { dateString += (hour < 12) ? 'am' : 'pm'; }

                // Uppercase Ante meridiem and Post meridiem - AM or PM
                else if (c === 'A') { dateString += (hour < 12) ? 'AM' : 'PM'; }

                // 12-hour format of an hour without leading zeros - 1 through 12
                else if (c === 'g') {
                    if (hour < 1) { dateString += '12'.toString(); }
                    else if (hour < 12) { dateString += hour.toString(); }
                    else if (hour === 12) { dateString += '12'.toString(); }
                    else { dateString += (hour - 12).toString(); }
                }

                // 24-hour format of an hour without leading zeros - 0 through 23
                else if (c === 'G') { dateString += hour.toString(); }

                // 12-hour format of an hour with leading zeros - 01 through 12
                else if (c === 'h') {
                    if (hour < 1) { dateString += '12'.toString(); }
                    else if (hour < 12) {
                        if (hour < 10) { dateString += '0'+hour.toString(); }
                        else { dateString += hour.toString(); }
                    }
                    else if (hour === 12) { dateString += '12'.toString(); }
                    else {
                        if ((hour - 12) < 10) { dateString += '0'+(hour - 12).toString(); }
                        else { dateString += (hour - 12).toString(); }
                    }
                }

                // 24-hour format of an hour with leading zeros - 00 through 23
                else if (c === 'H') { dateString += (hour < 10) ? '0'+hour.toString() : hour.toString(); }

                // Minutes with leading zeros - 00 to 59
                else if (c === 'i') { dateString += minute; }

                // Seconds, with leading zeros - 00 through 59
                else if (c === 's') { dateString += second; }

                else if (c === 'U') { dateString += (dateObj.getTime() / 1000).toString(); }

                // Append non-formatting strings as they are.
                else { dateString += c.toString(); }
            }

            else {
                dateString += c;
                escapeHolder = false;
            }
        }
        return dateString;
    },


    // Returns a JS Object from any variables passed in a GET URL
    urlGetVars: function () {
        'use strict';
        var i = 0,
            args = {},
            sets = [],
            pairs = [],
            query = location.search.substring(1);

        sets = query.split("&");

        for (i = 0; i < sets.length; i += 1) {
            pairs = sets[i].split("=");
            if (pairs[0] && pairs[1]) { args[pairs[0]] = decodeURIComponent(pairs[1]); }
        }
        return args;
    },


    // Returns the type of the passed in variable
    toType: function (obj) {
        'use strict';
        return ({}).toString.call(obj).match(/\s([a-zA-Z]+)/)[1].toLowerCase();
    },


    // Get the number of elements in an object since the length property does not work
    objectSize: function (obj) {
        'use strict';
        var size = 0,
            key,
            objType;

        objType = dhf.util.toType(obj);
        if (objType === 'object') { for (key in obj) { if (obj.hasOwnProperty(key)) { size += 1; } } }
        else if (objType === 'array') { size = obj.length; }
        return size;
    },


    // Add or Remove a value from a seperated string
    concatSepString: function (fieldId, catValue, addOrSubtract, sep) {
        'use strict';
        var fieldElement,
            valuesArray = [],
            tmpArray = [],
            i = 0;

        if ($D(fieldId)) {
            fieldElement = $D(fieldId);
            valuesArray = fieldElement.value.split(sep);
            if (addOrSubtract === 'ADD') {
                valuesArray.push(catValue.toString());
                fieldElement.value = valuesArray.join(sep);
            }
            else if (addOrSubtract === 'SUBTRACT') {
                for (i = 0; i < valuesArray.length; i += 1) {
                    if (catValue.toString() !== valuesArray[i].toString()) {
                        tmpArray.push(valuesArray[i].toString());
                    }
                }
                fieldElement.value = tmpArray.join(sep);
            }
        }
    },


    remove: function (id) {
        'use strict';
        var ele = document.getElementById(id);
        if (ele) { ele.parentNode.removeChild(ele); }
    },


    formatNumber: function(inputNumber) {
        'use strict';
        var numberLength = 0,
            numberOfCommas = 0,
            numberOfPreNumbers = 0,
            formattedNumber = '',
            i = 0;

        inputNumber = (inputNumber) ? parseInt(inputNumber, 10).toString() : '0';
        numberLength = parseInt(inputNumber.length, 10);
        numberOfCommas = parseInt((numberLength / 3), 10);
        numberOfPreNumbers = parseInt((numberLength % 3), 10);
        formattedNumber = inputNumber.substring(0, numberOfPreNumbers);
        for (i = 0; i < numberOfCommas; i += 1) {
            if (formattedNumber.length > 0) { formattedNumber += ','; }
            formattedNumber += inputNumber.substring(numberOfPreNumbers, (numberOfPreNumbers + 3));
            numberOfPreNumbers += 3;
        }
        return formattedNumber;
    },


    formatMoneyUS: function (inputNumber) {
        'use strict';
        var dollarLength = 0,
            dollarString = '',
            centLength = 0,
            centString = '',
            numberOfCommas = 0,
            numberOfPreNumbers = 0,
            formattedNumber = '',
            dollarCent = [],
            i = 0;

        inputNumber = (inputNumber) ? Math.round(inputNumber*Math.pow(10, 2))/Math.pow(10, 2).toString() : '0.00';
        dollarCent = inputNumber.toString().split('.');

        dollarString = (dollarCent[0]) ? dollarCent[0].toString() : '0'.toString();
        centString = (dollarCent[1]) ? dollarCent[1].toString() : '00'.toString();

        dollarLength = dollarString.length;
        centLength = centString.length;

        numberOfCommas = parseInt((dollarLength / 3), 10);

        numberOfPreNumbers = parseInt((dollarLength % 3), 10);

        formattedNumber = dollarString.substring(0, numberOfPreNumbers);

        for (i = 0; i < numberOfCommas; i += 1) {
            if (formattedNumber.length > 0) { formattedNumber += ','; }
            formattedNumber += dollarString.substring(numberOfPreNumbers, (numberOfPreNumbers + 3)).toString();
            numberOfPreNumbers += 3;
        }
        if (centLength === 0) { centString = '.00'.toString(); }
        else if (centLength === 1) { centString = '.'+centString+'0'; }
        else { centString = '.'+centString; }
        return '$'+formattedNumber+centString;
    },


    round: function (number, X) {
        'use strict';
        X = (!X ? 2 : X);
        return Math.round(number*Math.pow(10, X))/Math.pow(10, X);
    },


    validateInteger: function (num) {
        'use strict';
        var returnValue = false;

        if (parseInt(num, 10) === 0) { returnValue = false; }
        else { returnValue = (/^[\-]?[0-9]+$/).test(num.toString()); }
        return returnValue;
    },


    showHelp: function (msg) {
        'use strict';
        dhf.popup.make({ "title": 'Web Form Help' });
        dhf.popup.appendErrorLine(msg, 'INFO');
        dhf.popup.open();
    },


    displayWaitDiv: function (displayString, targetParent) {
        'use strict';
        var parentHeight = '',
            newTop;

        if ($D('dhf-waitDiv')) { jQuery("#dhf-waitDiv").remove(); }

        if (targetParent && ($D(targetParent))) {
            parentHeight = (jQuery($D(targetParent)).height() - 200).toString();
            newTop = 'top: -'+parentHeight+'px;';
        }
        else {
            parentHeight = '200';
            newTop = 'top: 300px;';
        }

        dhf.makeDiv({
            oid: 'dhf-waitDiv',
            iHTML: displayString || 'Searching.....',
            ocn: 'dhf-searching',
            oStyle: newTop,
            parentId: targetParent || 'documentBody'
        });
    },


    removeWaitDiv: function () {
        'use strict';
        if ($D('dhf-waitDiv')) { jQuery("#dhf-waitDiv").remove(); }
    },


    getMovieHtml: function (movieId, movieTitle) {
        'use strict';
        var shortMovieTitle = '';

        shortMovieTitle = (movieTitle.length > 35) ? movieTitle.substring(0, 32)+'...' : movieTitle;
        return '<a href="/editMovie.php?movieId='+movieId+'" title="'+movieTitle+'" target="_blank">'+shortMovieTitle+'</a>';
    },


    getActorHtml: function (actorId, actorName) {
        'use strict';
        var shortActorName = '';

        shortActorName = (actorName.length > 55) ? actorName.substring(0, 52)+'...' : actorName;
        return '<a href="/editActor.php?actorId='+actorId+'" title="'+actorName+'" target="_blank">'+shortActorName+'</a>';
    },


    sortArray: function (inputArray, sortField, reverseSort) {
        'use strict';
        var aValue,
            bValue,
            aDate,
            bDate,
            aDateO,
            bDateO,
            stringA,
            stringB,
            sortReturnValue = 0,
            regInt = /^[\-]?[0-9]+$/,
            regFloat = /^[\-]?[0-9]*\.?[0-9]+$/,
            testDate1 = /^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/,
            searchDate1 = /^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/,
            testDate2 = /^[0-9]{4}-[0-9]{2}-[0-9]{2}$/,
            searchDate2 = /^([0-9]{4})-([0-9]{2})-([0-9]{2})$/,
            testDateTime = /^[0-9]{2}\/[0-9]{2}\/[0-9]{4}\s[0-9]{2}:[0-9]{2}:[0-9]{2}$/,
            searchDateTime = /^([0-9]{2})\/([0-9]{2})\/([0-9]{4})\s([0-9]{2}):([0-9]{2}):([0-9]{2})$/;

        Logger.logGroupCollapsed("Sorting on '"+sortField+"'");
        reverseSort = reverseSort || 'N';
        if (!inputArray || dhf.util.toType(inputArray) !== 'array' || inputArray.length < 2) {
            Logger.log("Input Array is not an array - BAILING OUT!");
            return inputArray;
        }
        else if (!sortField) {
            Logger.log("No sort key provided - BAILING OUT!");
            return inputArray;
        }
        Logger.logGroupCollapsed("Reverse Sort '"+((reverseSort === 'Y') ? 'ON' : 'OFF')+"'");
        inputArray.sort(function(a,b) {
            if (!a || !b) {
                Logger.log("No a or b objects to sort - BAILING OUT!");
                return 0;
            }
            else if (a[sortField] === undefined || b[sortField] === undefined) {
                Logger.log("No elements to compare - BAILING OUT!");
                return 0;
            }
            // Set the sort values to a variable and an empty string if null
            aValue = (a[sortField] === null) ? '' : a[sortField];
            bValue = (b[sortField] === null) ? '' : b[sortField];
            // Test for Dates in format MM/DD/YYYY
            if (testDate1.test(aValue) === true && testDate1.test(bValue) === true) {
                Logger.log("Sorting '"+aValue+"' against '"+bValue+"' as Dates MM/DD/YYYY");
                aDate = aValue.match(searchDate1);
                bDate = bValue.match(searchDate1);
                aDateO = new Date(parseInt(aDate[3], 10), (dhf.util.round(aDate[1]) -1), parseInt(aDate[2], 10));
                bDateO = new Date(parseInt(bDate[3], 10), (dhf.util.round(bDate[1]) -1), parseInt(bDate[2], 10));
                if (aDateO.getTime() === bDateO.getTime()) {
                    if (a.objectId !== undefined) {
                        Logger.log("Dates were even - trying to sort on objectId");
                        return (parseInt(a.objectId, 10) < parseInt(b.objectId, 10)) ? 1 : -1;
                    }
                    else { return 0; }
                }
                else {
                    if (reverseSort === 'Y') { return (aDateO.getTime() < bDateO.getTime()) ? -1 : 1; }
                    else { return (aDateO.getTime() < bDateO.getTime()) ? 1 : -1; }
                }
            }
            // Test for Date in format YYYY-MM-DD
            else if (testDate2.test(aValue) === true && testDate2.test(bValue) === true) {
                Logger.log("Sorting '"+aValue+"' against '"+bValue+"' as Dates YYYY-MM-DD");
                aDate = aValue.match(searchDate2);
                bDate = bValue.match(searchDate2);
                aDateO = new Date(parseInt(aDate[1], 10), (dhf.util.round(aDate[2]) -1), parseInt(aDate[3], 10));
                bDateO = new Date(parseInt(bDate[1], 10), (dhf.util.round(bDate[2]) -1), parseInt(bDate[3], 10));
                if (aDateO.getTime() === bDateO.getTime()) {
                    if (a.objectId !== undefined) {
                        Logger.log("Dates were even - trying to sort on objectId");
                        return (parseInt(a.objectId, 10) < parseInt(b.objectId, 10)) ? 1 : -1;
                    }
                    else { return 0; }
                }
                else {
                    if (reverseSort === 'Y') { return (aDateO.getTime() < bDateO.getTime()) ? -1 : 1; }
                    else { return (aDateO.getTime() < bDateO.getTime()) ? 1 : -1; }
                }
            }
            // Test for Date/Time in format MM/DD/YYYY HH:MM:SS
            else if (testDateTime.test(aValue) === true && testDateTime.test(bValue) === true) {
                Logger.log("Sorting '"+aValue+"' against '"+bValue+"' as Date/Time MM/DD/YYY HH:MM:SS");
                aDate = aValue.match(searchDateTime);
                bDate = bValue.match(searchDateTime);
                aDateO = new Date(parseInt(aDate[3], 10), (dhf.util.round(aDate[1]) -1), parseInt(aDate[2], 10), parseInt(aDate[4], 10), parseInt(aDate[5], 10), parseInt(aDate[6], 10), 0);
                bDateO = new Date(parseInt(bDate[3], 10), (dhf.util.round(bDate[1]) -1), parseInt(bDate[2], 10), parseInt(bDate[4], 10), parseInt(bDate[5], 10), parseInt(bDate[6], 10), 0);
                if (aDateO.getTime() === bDateO.getTime()) {
                    if (a.objectId !== undefined) {
                        Logger.log("Dates were even - trying to sort on objectId");
                        return (parseInt(a.objectId, 10) < parseInt(b.objectId, 10)) ? 1 : -1;
                    }
                    else { return 0; }
                }
                else {
                    if (reverseSort === 'Y') { return (aDateO.getTime() < bDateO.getTime()) ? -1 : 1; }
                    else { return (aDateO.getTime() < bDateO.getTime()) ? 1 : -1; }
                }
            }
            // Test for Integers
            else if (regInt.test(aValue) === true && regInt.test(bValue) === true) {
                Logger.log("Sorting '"+aValue+"' against '"+bValue+"' as Integers");
                if (parseInt(aValue, 10) === parseInt(bValue, 10)) { return 0; }
                else {
                    if (reverseSort === 'Y') { return (parseInt(aValue, 10) < parseInt(bValue, 10)) ? -1 : 1; }
                    else { return (parseInt(aValue, 10) < parseInt(bValue, 10)) ? 1 : -1; }
                }
            }
            // Test for Floats
            else if (regFloat.test(aValue) === true && regFloat.test(bValue) === true) {
                Logger.log("Sorting '"+aValue+"' against '"+bValue+"' as Floats");
                if (parseFloat(aValue) === parseFloat(bValue)) { return 0; }
                else {
                    if (reverseSort === 'Y') { return (parseFloat(aValue) < parseFloat(bValue)) ? -1 : 1; }
                    else { return (parseFloat(aValue) < parseFloat(bValue)) ? 1 : -1; }
                }
            }
            // Default sort as Strings
            else {
                Logger.log("Sorting '"+aValue+"' against '"+bValue+"' as Strings");
                stringA = aValue.toUpperCase();
                stringB = bValue.toUpperCase();
                if (stringA === stringB) { return 0; }
                else {
                    if (reverseSort === 'Y') { return (stringA < stringB) ? 1 : -1; }
                    else { return (stringA < stringB) ? -1 : 1; }
                }
            }
        });
        Logger.logGroupEnd("Sorting on '"+sortField+"'");
        Logger.logGroupEnd("Reverse Sort '"+((reverseSort === 'Y') ? 'ON' : 'OFF')+"'");
        return inputArray;
    }

};

/**
 * Creates the instance of the class
 * @param object init - Start up values, only one option now
 * @return void
 */
var Logger = {

    logging: 'OFF',

    /**
     * Allows turning the logging on or off after the instance is created
     * Only allows loggin to be on if console exists
     * @param string inVal - Basically a Boolean ON or OFF
     * @return void
     */
    setLogging: function(inVal) {
        'use strict';
        Logger.logging = (window.console && inVal === 'ON') ? 'ON' : 'OFF';
        return;
    },

    /**
     * Allows grouping in the log if using Firebug, seperates groups with empty line otherwise
     * @param string groupName
     * @return void
     */
    logGroup: function(groupName) {
        'use strict';
        if (Logger.logging === 'ON') {
            if (console.group) { console.group(groupName); }
            else { console.log(" "); }
        }
    },

    /**
     * Allows collapsed grouping in the log if using Firebug, seperates groups with empty line otherwise
     * @param string groupName
     * @return void
     */
    logGroupCollapsed: function(groupName) {
        'use strict';
        if (Logger.logging === 'ON') {
            if (console.groupCollapsed) { console.groupCollapsed(groupName); }
            else { console.log(" "); }
        }
    },

    /**
     * Marks the end of a log group if using Firebug, inserts an empty line otherwise
     * @param string groupName
     * @return void
     */
    logGroupEnd: function(groupName) {
        'use strict';
        if (Logger.logging === 'ON') {
            if (console.groupEnd) { console.groupEnd(groupName); }
            else { console.log(" "); }
        }
    },

    /**
     * Creates a log entry with a debug icon if using Firbug, creates normal entry with DEBUG: prefix otherwise
     * @param string textToLog
     * @return void
     */
    logDebug: function(textToLog) {
        'use strict';
        if (Logger.logging === 'ON') {
            if (console.debug) { console.debug(textToLog); }
            else { console.log("DEBUG: "+textToLog); }
        }
    },

    /**
     * Creates a log entry with an info icon if using Firbug, creates normal entry with INFO: prefix otherwise
     * @param string textToLog
     * @return void
     */
    logInfo: function(textToLog) {
        'use strict';
        if (Logger.logging === 'ON') {
            if (console.info) { console.info(textToLog); }
            else { console.log("INFO: "+textToLog); }
        }
    },

    /**
     * Creates a log entry with an warning icon if using Firbug, creates normal entry with WARN: prefix otherwise
     * @param string textToLog
     * @return void
     */
    logWarn: function(textToLog) {
        'use strict';
        if (Logger.logging === 'ON') {
            if (console.warn) { console.warn(textToLog); }
            else { console.log("WARN: "+textToLog); }
        }
    },

    /**
     * Creates a log entry with an error icon if using Firbug, creates normal entry with ERROR: prefix otherwise
     * @param string textToLog
     * @return void
     */
    logError: function(textToLog) {
        'use strict';
        if (Logger.logging === 'ON') {
            if (console.error) { console.error(textToLog); }
            else { console.log("ERROR: "+textToLog); }
        }
    },

    /**
     * Creates a plain log entry is loggin is on and supported
     * @param string textToLog
     * @return void
     */
    log: function(textToLog) {
        'use strict';
        if (Logger.logging === 'ON') { console.log(textToLog); }
    }
};
