import React from 'react';
import { useNavigate } from 'react-router-dom';

const SignUp: React.FC = () => {
  const navigate = useNavigate();

  const handleSignUp = (e: React.FormEvent) => {
    e.preventDefault();
    // Add your sign-up logic here (validation, backend call, etc.)
    // For now, simulate success:
    navigate('/dashboard');
  };

  return (
    <div className="container mt-5">
      <h2 className="text-center mb-4">Create an Account</h2>
      <form onSubmit={handleSignUp}>
        <div className="mb-3">
          <label>Email</label>
          <input type="email" className="form-control" required />
        </div>
        <div className="mb-3">
          <label>Password</label>
          <input type="password" className="form-control" required />
        </div>
        <button type="submit" className="btn btn-primary w-100">Sign Up</button>
      </form>
    </div>
  );
};

export default SignUp;
