export const isStudent = state => {
  const roles = state.user.roles || [];
  return roles.includes('ROLE_USER');
};

export const isTeacher = state => {
  const roles = state.user.roles || [];
  return roles.includes('ROLE_TEACHER');
};

export const isAdmin = state => {
  const roles = state.user.roles || [];
  return roles.includes('ROLE_ADMIN');
};

export const isSuperAdmin = state => {
  const roles = state.user.roles || [];
  return roles.includes('ROLE_SUPER_ADMIN');
};
