import numpy as np
from scipy.optimize import fsolve

def f(x):
    print(f"f({x}) = {x + np.log2(2**(2*x) + 1) - 30}")
    return x + np.log2(2**(2*x) + 1) - 30

x0 = 25
solution = fsolve(f, x0)
print(f"Solution ≈ {solution[0]:.6f}")
