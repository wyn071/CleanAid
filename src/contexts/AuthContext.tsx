import React, { createContext, useContext, useState, useEffect } from 'react';
import { useNavigate, useLocation } from 'react-router-dom';

interface User {
  id: string;
  email: string;
  name: string;
  role: 'admin';
}

interface AuthContextType {
  user: User | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  updateProfile: (data: Partial<User>) => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const navigate = useNavigate();
  const location = useLocation();

  useEffect(() => {
    const token = localStorage.getItem('authToken');
    if (token) {
      // TODO: Replace with actual token validation
      const mockUser: User = {
        id: '1',
        email: 'admin@dswd.gov.ph',
        name: 'Admin User',
        role: 'admin',
      };
      setUser(mockUser);
    }
    setLoading(false);
  }, []);

  const login = async (email: string, password: string) => {
    if (email === 'admin@dswd.gov.ph' && password === 'admin123') {
      const mockUser: User = {
        id: '1',
        email: 'admin@dswd.gov.ph',
        name: 'Admin User',
        role: 'admin',
      };
      setUser(mockUser);
      localStorage.setItem('authToken', 'mock-token');

      // Redirect to original destination or dashboard
      const origin = (location.state as any)?.from?.pathname || '/dashboard';
      navigate(origin, { replace: true });
    } else {
      throw new Error('Invalid credentials');
    }
  };

  const logout = async () => {
    localStorage.removeItem('authToken');
    setUser(null);
    navigate('/login');
  };

  const updateProfile = async (data: Partial<User>) => {
    if (user) {
      setUser({ ...user, ...data });
    }
  };

  return (
    <AuthContext.Provider value={{ user, loading, login, logout, updateProfile }}>
      {children}
    </AuthContext.Provider>
  );
};

export default AuthContext;
