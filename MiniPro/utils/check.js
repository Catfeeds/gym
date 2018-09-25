function stringTrim(str) {
  str = str.replace(/^\s\s*/, '');
  var ws = /\s/;
  var i = str.length;
  while (ws.test(str.charAt(--i)));
  return str.slice(0, i + 1);
}

module.exports = {
  stringTrim: stringTrim
}