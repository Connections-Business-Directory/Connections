!function(e){"function"==typeof define&&define.amd?define(["jquery","../jquery.validate"],e):"object"==typeof module&&module.exports?module.exports=e(require("jquery")):e(jQuery)}((function(e){return e.extend(e.validator.messages,{required:"השדה הזה הינו שדה חובה",remote:"נא לתקן שדה זה",email:'נא למלא כתובת דוא"ל חוקית',url:"נא למלא כתובת אינטרנט חוקית",date:"נא למלא תאריך חוקי",dateISO:"נא למלא תאריך חוקי (ISO)",number:"נא למלא מספר",digits:"נא למלא רק מספרים",creditcard:"נא למלא מספר כרטיס אשראי חוקי",equalTo:"נא למלא את אותו ערך שוב",extension:"נא למלא ערך עם סיומת חוקית",maxlength:e.validator.format(".נא לא למלא יותר מ- {0} תווים"),minlength:e.validator.format("נא למלא לפחות {0} תווים"),rangelength:e.validator.format("נא למלא ערך בין {0} ל- {1} תווים"),range:e.validator.format("נא למלא ערך בין {0} ל- {1}"),max:e.validator.format("נא למלא ערך קטן או שווה ל- {0}"),min:e.validator.format("נא למלא ערך גדול או שווה ל- {0}")}),e}));