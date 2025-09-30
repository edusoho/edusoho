const filterTF = data => {
  switch (data) {
    case "T":
      return "对";
    case "F":
      return "错";
    default:
      return "";
  }
};

export { filterTF };
