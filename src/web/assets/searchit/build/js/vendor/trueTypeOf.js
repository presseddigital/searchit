/*!
 * More accurately check the type of a JavaScript object
 * (c) 2018 Chris Ferdinandi, MIT License, https://gomakethings.com
 * Docs: https://gomakethings.com/true-type-checking-with-vanilla-js/
 * @param  {Object} obj The object
 * @return {String}     The object type
 */
var trueTypeOf = function (obj) {
	return Object.prototype.toString.call(obj).slice(8, -1).toLowerCase();
};
