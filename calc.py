import numpy as np
from scipy.optimize import fsolve

# Définir la fonction à annuler
def f(x):
    return x + np.log2(2**(2*x) + 1) - 30

# Estimation initiale
x0 = 23


# Résolution numérique
solution = fsolve(f, x0)

print(f"La solution est x ≈ {solution[0]:.6f}")
