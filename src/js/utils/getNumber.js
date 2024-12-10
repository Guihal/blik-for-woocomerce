function getNumber(number) {
  if (typeof number != "number") {
    if (typeof number != "string") return false;

    number = Number(number.trim().replaceAll(/[^0-9]{1,}/g, ""));

    if (isNaN(number)) return false;

    return number;
  }

  return number;
}
